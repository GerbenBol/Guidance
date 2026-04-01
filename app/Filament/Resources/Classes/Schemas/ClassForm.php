<?php

namespace App\Filament\Resources\Classes\Schemas;

use App\Enums\Ability;
use App\Models\PlayerClass;
use App\Models\School;
use App\Models\Spell;
use App\Services\FeatureService;
use App\Services\FormService;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class ClassForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(function (): array {
                $operation = explode('/', request()->url());
                $operation = $operation[count($operation) - 1];

                $general = [
                    TextInput::make('name')
                        ->afterLabel(FormService::makeHintIcon('The name of the class'))
                        ->required(),
                    TextInput::make('source')
                        ->afterLabel(FormService::makeHintIcon('Original source of this item, such as "Tasha\'s Cauldron of Everything" or Homebrew')),
                    Textarea::make('short_desc')
                        ->label('Short Description')
                        ->afterLabel(FormService::makeHintIcon('A short description, which is shown in the character builder and in the class overview')),
                    Textarea::make('description')
                        ->afterLabel(FormService::makeHintIcon('The full description')),
                ];

                if ($operation == 'create') {
                    return $general;
                } else {
                    return [
                        Wizard::make([
                            Step::make('General')
                                ->schema($general),
                            Step::make('Features')
                                ->schema([
                                    Repeater::make('features')
                                        ->hiddenLabel()
                                        ->schema(FeatureService::singleFormItem())
                                        ->reorderable(false),
                                ]),
                            Step::make('Spellcasting')
                                ->schema([
                                    Section::make()
                                        ->schema([
                                            Checkbox::make('can_cast_spells'),
                                            Select::make('spell_ability')
                                                ->label('Spellcasting Ability')
                                                ->options(Ability::class)
                                                ->native(false),
                                            Radio::make('has_own_spelllist')
                                                ->hiddenLabel()
                                                ->live()
                                                ->inline()
                                                ->boolean('Own Spell List', 'Borrows Spell List'),
                                            Section::make('Own Spell List')
                                                ->schema([
                                                    Hidden::make('spell_filters'),
                                                    Select::make('spells')
                                                        ->label('Add Spells')
                                                        ->multiple()
                                                        ->options(function (Get $get): array|Collection {
                                                            $filters = json_decode($get('spell_filters'));
                                                            $collection = Spell::all();

                                                            if ($filters) {
                                                                $query = Spell::query();

                                                                foreach ($filters as $filter => $allowed) {
                                                                    switch ($filter) {
                                                                        case 'school':
                                                                            $query = $query->whereIn('school_id', $allowed);
                                                                            break;
                                                                        case 'level':
                                                                            $query = $query->whereIn('level', $allowed);
                                                                            break;
                                                                        default: break;
                                                                    }
                                                                }
                                                                $collection = $query->get();
                                                            }

                                                            return $collection->pluck('name', 'id');
                                                        })
                                                        ->searchable()
                                                        ->native(false)
                                                        ->suffixAction(
                                                            Action::make('filter')
                                                                ->tooltip('Add Filters')
                                                                ->icon(Heroicon::Funnel)
                                                                ->fillForm(fn (Get $get): array => json_decode($get('spell_filters'), true) ?? [])
                                                                ->schema([
                                                                    Select::make('school')
                                                                        ->label('Schools of Magic')
                                                                        ->options(School::all()->pluck('name', 'id'))
                                                                        ->multiple(),
                                                                    Select::make('level')
                                                                        ->label('Spell Level')
                                                                        ->options([
                                                                            'Cantrip', 1, 2, 3,
                                                                            4, 5, 6, 7, 8, 9,
                                                                        ])
                                                                        ->multiple(),
                                                                ])
                                                                ->modalSubmitActionLabel('Apply Filters')
                                                                ->action(fn (Set $set, array $data) => $set('spell_filters', json_encode($data))),
                                                            true
                                                        ),
                                                ])
                                                ->visible(fn (Get $get): bool => $get('has_own_spelllist') ?? false),
                                            Section::make('Borrows Spell List')
                                                ->schema([
                                                    Select::make('borrows_from')
                                                        ->options(fn (PlayerClass $record): array|Collection => PlayerClass::whereNot('id', $record->id)->pluck('name', 'id'))
                                                        ->searchable()
                                                        ->native(false)
                                                        ->suffixAction(
                                                            Action::make('checkoutSpellList')
                                                                ->icon(Heroicon::AcademicCap)
                                                                ->tooltip('View spell list')
                                                                ->schema([])
                                                                ->visible(fn (Get $get): bool => $get('borrows_from') != null),
                                                            true
                                                        ),
                                                ])
                                                ->visible(fn (Get $get) => $get('has_own_spelllist') != null && ! $get('has_own_spelllist')),
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
                                            ];

                                            for ($lvl = 1; $lvl <= 20; $lvl++) {
                                                $slots = [];

                                                for ($slot = 1; $slot <= 9; $slot++) {
                                                    $slots[] = FusedGroup::make([
                                                        Action::make('lvl'.$lvl.'slot'.$slot.'increase')
                                                            ->hiddenLabel()
                                                            ->icon(Heroicon::ChevronUp)
                                                            ->action(fn (Get $get, Set $set) => $set('lvl'.$lvl.'slot'.$slot, ($get('lvl'.$lvl.'slot'.$slot) ?: 0) + 1)),
                                                        TextInput::make('lvl'.$lvl.'slot'.$slot)
                                                            ->disabled(),
                                                        Action::make('lvl'.$lvl.'slot'.$slot.'decrease')
                                                            ->hiddenLabel()
                                                            ->icon(Heroicon::ChevronDown)
                                                            ->action(fn (Get $get, Set $set) => $set('lvl'.$lvl.'slot'.$slot, ($get('lvl'.$lvl.'slot'.$slot) ?: 0) - 1)),
                                                    ])
                                                        ->aboveContent(Schema::center(Text::make($slot)));
                                                }
                                                $rows[] = Fieldset::make('Character Level '.$lvl)
                                                    ->schema($slots)
                                                    ->columns(9);
                                            }

                                            return $rows;
                                        })
                                        // ->columns(2)
                                        // ->collapsed()
                                        ->secondary(),
                                ])
                                ->columns(2),
                        ])
                            ->skippable()
                            ->startOnStep(3)
                            ->columnSpanFull(),
                    ];
                }
            });
    }
}
