<?php

namespace App\Filament\Resources\Classes\Schemas;

use App\Enums\Ability;
use App\Models\PlayerClass;
use App\Services\FormService;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ClassForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(function (): array {
                $operation = explode('/', request()->url());
                $operation = $operation[count($operation) - 1];

                $general = [
                    Hidden::make('user_id')
                        ->default(Auth::user()->id),
                    Grid::make(4)
                        ->schema([
                            TextInput::make('name')
                                ->afterLabel(FormService::makeHintIcon('The name of the class'))
                                ->required()
                                ->columnSpan(3),
                            FormService::getSystemVersionInput(),
                        ])
                        ->columnSpanFull(),
                    // TextInput::make('source')
                    //     ->afterLabel(FormService::makeHintIcon('Original source of this item, such as "Tasha\'s Cauldron of Everything" or Homebrew')),
                    Textarea::make('short_desc')
                        ->label('Short Description')
                        ->afterLabel(FormService::makeHintIcon('A short description, which is shown in the character builder and in the class overview'))
                        ->columnSpanFull(),
                    Textarea::make('description')
                        ->afterLabel(FormService::makeHintIcon('The full description'))
                        ->columnSpanFull(),
                ];

                if ($operation == 'create') {
                    return $general;
                } else {
                    return [
                        Wizard::make([
                            Step::make('General')
                                ->schema($general)
                                ->columns(2),
                            Step::make('Features')
                                ->schema([
                                    Repeater::make('features')
                                        ->hiddenLabel()
                                        ->schema([])
                                        ->reorderable(false),
                                ]),
                            Step::make('Spellcasting')
                                ->schema([
                                    Section::make()
                                        ->schema([
                                            Checkbox::make('can_cast_spells'),
                                            Select::make('spell_ability')
                                                ->label('Spellcasting Ability')
                                                ->options(Ability::toArray())
                                                ->native(false),
                                            Radio::make('has_own_spelllist')
                                                ->hiddenLabel()
                                                ->live()
                                                ->inline()
                                                ->boolean('Own Spell List', 'Borrows Spell List'),
                                            Section::make('Own Spell List')
                                                ->schema(FormService::makeSpellSelectFull())
                                                ->visible(fn (Get $get): bool => $get('has_own_spelllist') ?? false),
                                            Section::make('Borrows Spell List')
                                                ->schema([
                                                    Select::make('borrows_from')
                                                        ->options(fn (PlayerClass $record): array|Collection => PlayerClass::whereNot('id', $record->id)->pluck('name', 'id'))
                                                        ->searchable()
                                                        ->native(false)
                                                        ->live()
                                                        ->suffixAction(
                                                            Action::make('checkoutSpellList')
                                                                ->icon(Heroicon::AcademicCap)
                                                                ->tooltip('View spell list')
                                                                ->schema([])
                                                                ->visible(fn (Get $get): bool => $get('borrows_from') != null),
                                                            true
                                                        ),
                                                    Hidden::make('spell_filters'),
                                                    FormService::makeSpellSelect('extra_spells', 'Additional Spells'),
                                                ])
                                                ->visible(fn (Get $get) => ! $get('has_own_spelllist')),
                                        ])
                                        ->secondary(),
                                    Section::make('Spell Slot Table')
                                        ->schema(function (): array {
                                            $rows = [
                                                TextInput::make('cantrips')
                                                    ->hiddenLabel()
                                                    ->prefix('Cantrips: ', true)
                                                    ->numeric()
                                                    ->columnSpanFull(),
                                                TextInput::make('spellslots'),
                                            ];

                                            // for ($lvl = 1; $lvl <= 20; $lvl++) {
                                            // $slots = [];

                                            // for ($slot = 1; $slot <= 9; $slot++) {
                                            //     $slots[] = TextInput::make('lvl'.$lvl.'slot'.$slot)
                                            //         ->hiddenLabel()
                                            //         ->disabled()
                                            //         ->aboveContent(Schema::center([
                                            //             Text::make($slot),
                                            //             Action::make('lvl'.$lvl.'slot'.$slot.'increase')
                                            //                 ->hiddenLabel()
                                            //                 ->icon(Heroicon::ChevronUp)
                                            //                 ->button()
                                            //                 ->action(fn (Get $get, Set $set) => $set('lvl'.$lvl.'slot'.$slot, ($get('lvl'.$lvl.'slot'.$slot) ?: 0) + 1)),
                                            //         ]))
                                            //         ->belowContent(
                                            //             Schema::center(
                                            //                 Action::make('lvl'.$lvl.'slot'.$slot.'decrease')
                                            //                     ->hiddenLabel()
                                            //                     ->icon(Heroicon::ChevronDown)
                                            //                     ->button()
                                            //                     ->action(fn (Get $get, Set $set) => $set('lvl'.$lvl.'slot'.$slot, ($get('lvl'.$lvl.'slot'.$slot) ?: 0) - 1)),
                                            //             )
                                            //         );
                                            // }
                                            // $rows[] = Fieldset::make('Character Level '.$lvl)
                                            //     ->schema([Flex::make($slots)->from('md')])
                                            //     ->columns(1);
                                            // }
                                            $rows[] = Actions::make(function (): array {
                                                $actions = [];

                                                for ($lvl = 1; $lvl <= 20; $lvl++) {
                                                    $actions[] = Action::make('lvl'.$lvl)
                                                        ->label('Character Level '.$lvl)
                                                        ->schema(function (): array {
                                                            $inputs = [];

                                                            for ($slot = 1; $slot <= 9; $slot++) {
                                                                $inputs[] = TextInput::make('slot'.$slot)
                                                                    ->hiddenLabel()
                                                                    ->prefix('Level '.$slot.':')
                                                                    ->numeric();
                                                            }

                                                            return [
                                                                Grid::make([
                                                                    'default' => 2,
                                                                    'sm' => 3,
                                                                ])
                                                                    ->schema($inputs),
                                                            ];
                                                        })
                                                        ->modalWidth(Width::Large)
                                                        ->modalSubmitActionLabel('Apply');
                                                }

                                                return $actions;
                                            })
                                                ->alignCenter();

                                            return $rows;
                                        })
                                        // ->collapsible()
                                        ->collapsed()
                                        ->secondary(),
                                ])
                                ->columns(2),
                        ])
                            ->skippable()
                            ->startOnStep(3)
                            ->columnSpanFull(),
                    ];
                }
            })
            ->columns(2);
    }
}
