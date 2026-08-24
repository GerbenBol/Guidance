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
use Filament\Forms\Components\Repeater;
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
                                        Grid::make(2)
                                            ->schema([
                                                Section::make('Primary Class')
                                                    ->schema([
                                                        Select::make('save_prof')
                                                            ->label('Saving Throw Proficiencies')
                                                            ->options(Ability::toArray())
                                                            ->multiple()
                                                            ->maxItems(2),
                                                        self::getProficiencyRepeater('prof'),
                                                    ])
                                                    ->secondary(),
                                                Section::make('Multiclass')
                                                    ->schema([self::getProficiencyRepeater('multiclass_prof')])
                                                    ->secondary(),
                                            ])
                                            ->columnSpanFull(),
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
                                                            ->action(fn (Set $set) => $set('asi_lvls', [4, 8, 12, 16]))
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
                                        Section::make('General')
                                            ->schema([
                                                Checkbox::make('can_cast_spells')
                                                    ->columnSpan(3),
                                                Select::make('spell_ability')
                                                    ->prefix('Spellcasting Ability')
                                                    ->hiddenLabel()
                                                    ->options(Ability::toArray())
                                                    ->native(false)
                                                    ->columnSpan(9),
                                                Radio::make('has_own_spelllist')
                                                    ->label('Has spell list')
                                                    ->live()
                                                    ->boolean('Own', 'Borrowed')
                                                    ->columnSpan(4),
                                                Radio::make('knows_full_spelllist')
                                                    ->label('Knows all spells in spell list')
                                                    ->live()
                                                    ->boolean()
                                                    ->default(false)
                                                    ->columnSpan(4),
                                                Radio::make('regains_spells_on')
                                                    ->live()
                                                    ->options([
                                                        'sh' => 'Short Rest',
                                                        'lr' => 'Long Rest',
                                                    ])
                                                    ->default('lr')
                                                    ->columnSpan(4),
                                                Section::make('Own Spell List')
                                                    ->schema(FormService::makeSpellSelectFull())
                                                    ->visible(fn (Get $get): bool => $get('has_own_spelllist') ?? false)
                                                    ->columnSpanFull(),
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
                                                    ->visible(fn (Get $get) => ! $get('has_own_spelllist'))
                                                    ->columnSpanFull(),
                                                Section::make(fn (Get $get): string => 'Spells '.(! $get('knows_full_spelllist') ? '& Cantrips Known' : 'Prepared & Cantrips Known').' at Level')
                                                    ->schema([
                                                        Select::make('active_class_level')
                                                            ->hiddenLabel()
                                                            ->live()
                                                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                                $amounts = json_decode($get('known_prepared_amounts'), true);
                                                                $cantrips = $spells = null;

                                                                for ($i = $state ?? 1; $i > 0; $i--) {
                                                                    if (isset($amounts[$i])) {
                                                                        if (! $cantrips && isset($amounts[$i]['cantrips'])) {
                                                                            $cantrips = $amounts[$i]['cantrips'];
                                                                        }
                                                                        if (! $spells && isset($amounts[$i]['spells'])) {
                                                                            $spells = $amounts[$i]['spells'];
                                                                        }
                                                                    }
                                                                }
                                                                $set('cantrips', $cantrips ?? 0);
                                                                $set('prepared_spells_amount', $spells ?? 0);
                                                            })
                                                            ->options(function (): array {
                                                                $ret = [];

                                                                for ($i = 1; $i <= 20; $i++) {
                                                                    $ret[$i] = 'Class Level '.$i;
                                                                }

                                                                return $ret;
                                                            })
                                                            ->formatStateUsing(fn (?string $state): string => $state ?? 1)
                                                            ->searchable()
                                                            ->prefixAction(
                                                                Action::make('previousLevel')
                                                                    ->icon(Heroicon::ChevronLeft)
                                                                    ->action(fn (Set $set, Get $get) => $set('active_class_level', $get('active_class_level') - 1))
                                                                    ->visible(fn (Get $get): bool => $get('active_class_level') != 1),
                                                                true
                                                            )
                                                            ->suffixAction(
                                                                Action::make('nextLevel')
                                                                    ->icon(Heroicon::ChevronRight)
                                                                    ->action(fn (Set $set, Get $get) => $set('active_class_level', $get('active_class_level') + 1))
                                                                    ->visible(fn (Get $get): bool => $get('active_class_level') != 20),
                                                                true
                                                            ),
                                                        Hidden::make('known_prepared_amounts')
                                                            ->default('{}'),
                                                        FusedGroup::make([
                                                            TextInput::make('cantrips')
                                                                ->prefix('Cantrips Known:')
                                                                ->live()
                                                                ->formatStateUsing(fn (Get $get): int => json_decode($get('known_prepared_amounts'), true)[$get('active_class_level') ?? 1]['cantrips'] ?? 0)
                                                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                                    $og = json_decode($get('known_prepared_amounts'), true);
                                                                    $og[$get('active_class_level') ?? 1]['cantrips'] = (int) $state;
                                                                    $set('known_prepared_amounts', json_encode($og));
                                                                })
                                                                ->numeric(),
                                                            TextInput::make('prepared_spells_amount')
                                                                ->prefix(fn (Get $get): string => $get('knows_full_spelllist') ? 'Prepared Spells:' : 'Known Spells:')
                                                                ->live()
                                                                ->formatStateUsing(fn (Get $get): int => json_decode($get('known_prepared_amounts'), true)[$get('active_class_level') ?? 1]['spells'] ?? 0)
                                                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                                    $og = json_decode($get('known_prepared_amounts'), true);
                                                                    $og[$get('active_class_level') ?? 1]['spells'] = (int) $state;
                                                                    $set('known_prepared_amounts', json_encode($og));
                                                                })
                                                                ->numeric(),
                                                        ])
                                                            ->columns(2),
                                                    ])
                                                    ->columnSpanFull(),
                                            ])
                                            ->secondary()
                                            ->columns(12),
                                        Section::make('Spell Slot Table')
                                            ->schema(function (): array {
                                                $rows = [
                                                    Hidden::make('spellslots'),
                                                    Hidden::make('open_section'),
                                                    Select::make('active_level')
                                                        ->hiddenLabel()
                                                        ->live()
                                                        ->native(false)
                                                        ->options(function (): array {
                                                            $lvls = [];

                                                            for ($i = 1; $i <= 20; $i++) {
                                                                $lvls[$i] = 'Class Level '.$i;
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
                                                ];

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

                                                                for ($i = $lvlslot[0] + 1; $i <= 20; $i++) {        // Also update following levels for QoL on the first time making spellcasting for a class
                                                                    $higherLevel = $slots[$i] ?? [];

                                                                    if (! isset($higherLevel[$lvlslot[1]]) || $higherLevel[$lvlslot[1]] < (int) $state) {
                                                                        $higherLevel[$lvlslot[1]] = (int) $state;
                                                                        $set($i.'_'.$lvlslot[1], (int) $state);
                                                                    }
                                                                    $slots[$i] = $higherLevel;
                                                                }
                                                                $set('spellslots', json_encode($slots));
                                                            });
                                                    }

                                                    $rows[] = Section::make()
                                                        ->schema($slotItems)
                                                        ->visible(fn (Get $get): bool => $get('active_level') ? $get('active_level') == $lvl : $lvl == 1);
                                                }

                                                return $rows;
                                            })
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

    private static function getProficiencyRepeater(string $name): Repeater
    {
        return Repeater::make($name)
            ->label('Proficiencies')
            ->schema([
                FusedGroup::make([
                    TextInput::make('amount')
                        ->prefix('Choose:')
                        ->numeric()
                        ->columnSpan(3),
                    Select::make('type')
                        ->prefix('Type:')
                        ->live()
                        ->options([
                            'skill' => 'Skills',
                            // 'save' => 'Saving Throws',
                            'tool' => 'Tools',
                            'weaptype' => 'Weapon Types',
                            'weapon' => 'Weapons',
                        ])
                        ->native(false)
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                        ->placeholder('Select type')
                        ->columnSpan(4),
                    Select::make('options')
                        ->prefix('From:')
                        ->options(fn (Get $get): array => match ($get('type')) {
                            'skill' => Skill::getAllRecords('publicOrOwned'),
                            // 'save' => Ability::saveArray(),
                            'tool' => [/* tools */],
                            'weaptype' => [/* weapon types */],
                            'weapon' => [/* weapons */],
                            default => [],
                        })
                        ->multiple()
                        ->columnSpan(5),
                ])
                    ->columns(12),
            ])
            ->reorderable(false)
            ->addActionLabel('Add proficiency');
    }
}
