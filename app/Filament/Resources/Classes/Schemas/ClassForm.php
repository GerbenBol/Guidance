<?php

namespace App\Filament\Resources\Classes\Schemas;

use App\Enums\Ability;
use App\Enums\Dice;
use App\Filament\Forms\Components\SpellSlotItem;
use App\Models\PlayerClass;
use App\Models\Skill;
use App\Services\FormService;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
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
                        Tabs::make()
                            ->tabs([
                                Tab::make('General')
                                    ->schema(array_merge($general, [
                                        Select::make('hit_die')
                                            ->label('Hit Die')
                                            ->options(Dice::toArray())
                                            ->native(false),
                                        FusedGroup::make([
                                            Select::make('primary_ability')
                                                ->prefix('Primary:')
                                                ->options(function (Get $get): array {
                                                    $abilities = Ability::toArray();

                                                    if ($get('secondary_ability') && $get('secondary_ability') != 'none') {
                                                        unset($abilities[$get('secondary_ability')]);
                                                    }

                                                    return $abilities;
                                                })
                                                ->live()
                                                ->native(false),
                                            Select::make('secondary_ability')
                                                ->prefix('Secondary:')
                                                ->options(function (Get $get): array {
                                                    $abilities = ['none' => 'No Secondary'] + Ability::toArray();

                                                    if ($get('primary_ability')) {
                                                        unset($abilities[$get('primary_ability')]);
                                                    }

                                                    return $abilities;
                                                })
                                                ->live()
                                                ->native(false),
                                        ])
                                            ->label('Primary Abilities')
                                            ->columns(2)
                                            ->columnSpan(2),
                                        Select::make('save_prof')
                                            ->label('Saving Throw Proficiencies')
                                            ->options(Ability::toArray())
                                            ->multiple()
                                            ->maxItems(2),
                                        FusedGroup::make([
                                            TextInput::make('amount_of_skill_prof')
                                                ->prefix('Choose:')
                                                ->numeric(),
                                            Select::make('skill_prof')
                                                ->prefix('From:')
                                                ->options(Skill::all()->pluck('name', 'id'))
                                                ->multiple()
                                                ->columnSpan(4),
                                        ])
                                            ->label('Skill Proficiencies')
                                            ->columns(5)
                                            ->columnSpan(2),
                                        // Select::make('other_prof')
                                        //     ->label('Other Proficiencies')
                                        //     ->options([])
                                        //     ->multiple()
                                        //     ->columnSpanFull(),
                                        // Select::make('start_equip')
                                        //     ->label('Starting Equipment')
                                        //     ->
                                    ]))
                                    ->columns(3),
                                Tab::make('Features')
                                    ->schema([
                                        Section::make('Ability Score Improvements')
                                            ->schema([
                                                Select::make('asi_lvls')
                                                    ->label('ASI Levels')
                                                    ->options(function (): array {
                                                        $lvls = [];

                                                        for ($i = 1; $i <= 20; $i++) {
                                                            $lvls[$i] = 'Level '.$i;
                                                        }

                                                        return $lvls;
                                                    })
                                                    ->default([4, 8, 12, 16])
                                                    ->multiple()
                                                    ->reorderable()
                                                    ->suffixAction(
                                                        Action::make('setDefault')
                                                            ->tooltip('Set default (4, 8, 12, 16)')
                                                            ->icon(Heroicon::Backward)
                                                            ->action(fn (Set $set) => $set('asi_lvls', [4, 6, 12, 16]))
                                                    ),
                                            ])
                                            ->collapsible()
                                            ->secondary(),
                                        Section::make('Subclass')
                                            ->schema([
                                                TextInput::make('subclass_name')
                                                    ->label('Feature Name')
                                                    ->afterLabel(FormService::makeHintIcon('Examples: Arcane Tradition or Bard College')),
                                                Select::make('subclass_start_lvl')
                                                    ->label('First Level of Subclass')
                                                    ->options(function (): array {
                                                        $lvls = [];

                                                        for ($i = 1; $i <= 20; $i++) {
                                                            $lvls[$i] = 'Level '.$i;
                                                        }

                                                        return $lvls;
                                                    })
                                                    ->searchable(),
                                            ])
                                            ->collapsible()
                                            ->columns(2)
                                            ->secondary(),
                                        FormService::getFeatureRepeater()
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                                Tab::make('Spellcasting')
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
                                                    Grid::make(2)
                                                        ->schema([
                                                            TextInput::make('cantrips')
                                                                ->numeric(),
                                                            Select::make('cantrip_upgrades')
                                                                ->multiple()
                                                                ->options(function (): array {
                                                                    $ret = [];

                                                                    for ($i = 1; $i <= 20; $i++) {
                                                                        $ret[$i] = 'Level '.$i;
                                                                    }

                                                                    return $ret;
                                                                }),
                                                        ]),
                                                    Hidden::make('spellslots'),
                                                ];

                                                $rows[] = Hidden::make('open_section');
                                                for ($lvl = 1; $lvl <= 20; $lvl++) {
                                                    $slotItems = [];

                                                    for ($slot = 1; $slot <= 9; $slot++) {
                                                        $slotItems[] = SpellSlotItem::make($lvl.'_'.$slot)
                                                            ->label('Slot Level '.$slot)
                                                            ->hiddenLabel()
                                                            ->live()
                                                            ->dehydrated(false)
                                                            ->afterStateUpdated(function (SpellSlotItem $component, string $state, Set $set, Get $get) {
                                                                $slots = json_decode($get('spellslots'), true);     // Get current spell slots
                                                                $lvlslot = explode('_', $component->getName());     // Get component name, which includes character level & slot level
                                                                $level = $slots[$lvlslot[0]] ?? [];                 // Get current data for character level
                                                                $level[$lvlslot[1]] = (int) $state;                 // Set data for spell slots at character level
                                                                $slots[$lvlslot[0]] = $level;                       // Reinject data into all spell slots
                                                                $set('spellslots', json_encode($slots));
                                                            });
                                                    }
                                                    $rows[] = Section::make()
                                                        ->schema($slotItems)
                                                        ->afterHeader([
                                                            Select::make('active_level')
                                                                ->hiddenLabel()
                                                                ->live()
                                                                ->native(false)
                                                                ->options(function (): array {
                                                                    $lvls = [];

                                                                    for ($i = 1; $i <= 20; $i++) {
                                                                        $lvls[$i] = 'Character Level '.$i;
                                                                    }

                                                                    return $lvls;
                                                                })
                                                                ->formatStateUsing(fn (?string $state): string => $state ?? 1)
                                                                ->prefixAction(
                                                                    Action::make('previousSection')
                                                                        ->icon(Heroicon::ChevronLeft)
                                                                        ->action(fn (Set $set, Get $get) => $set('active_level', $get('active_level') - 1))
                                                                        ->visible(fn (Get $get): bool => $get('active_level') != 1),
                                                                    true
                                                                )
                                                                ->suffixAction(
                                                                    Action::make('nextSection')
                                                                        ->icon(Heroicon::ChevronRight)
                                                                        ->action(fn (Set $set, Get $get) => $set('active_level', $get('active_level') + 1))
                                                                        ->visible(fn (Get $get): bool => $get('active_level') != 20),
                                                                    true
                                                                ),
                                                        ])
                                                        ->visible(fn (Get $get): bool => $get('active_level') ? $get('active_level') == $lvl : $lvl == 1);
                                                }

                                                return $rows;
                                            })
                                            // ->collapsible()
                                            ->collapsed()
                                            ->secondary(),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpanFull(),
                    ];
                }
            })
            ->columns(2);
    }
}
