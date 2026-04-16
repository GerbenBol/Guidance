<?php

namespace App\Services;

use App\Enums\Ability;
use App\Models\DamageType;
use App\Models\School;
use App\Models\Skill;
use App\Models\Spell;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class FormService
{
    /**
     * Returns a schema with a hint icon with a tooltip.
     *
     * @param  string  $tooltip  Optional. Sets the tooltip text.
     * @param  string  $position  Optional. Sets the placement of the schema, defaults to `start`.
     * @return Schema Returns a schema with a hint icon.
     */
    public static function makeHintIcon(string $tooltip = '', string $position = 'start'): Schema
    {
        return Schema::$position(Icon::make(Heroicon::QuestionMarkCircle)->tooltip($tooltip));
    }

    /**
     * Automatically creates a select for selecting spells, and includes
     * the hidden input.
     *
     * @param  string  $name  Optional. Sets the name of the select, defaults to `spells`.
     * @param  string  $label  Optional. Sets the label of the select, defaults to `Spell List`.
     * @param  bool  $multiple  Optional. Sets if the select can/cannot have multiple selected choices, on by default.
     * @return array Returns the created array with the hidden and select inputs.
     */
    public static function makeSpellSelectFull(string $name = 'spells', string $label = 'Spell List', bool $multiple = true): array
    {
        return [
            Hidden::make('spell_filters'),
            self::makeSpellSelect($name, $label, $multiple),
        ];
    }

    /**
     * Automatically creates a select for selecting spells. A hidden input
     * with name `spell_filters` should be included, otherwise the filters
     * will break.
     *
     * @param  string  $name  Optional. Sets the name of the select, defaults to `spells`.
     * @param  string  $label  Optional. Sets the label of the select, defaults to `Spell List`.
     * @param  bool  $multiple  Optional. Sets if the select can/cannot have multiple selected choices, on by default.
     * @return Select Returns the created select input.
     */
    public static function makeSpellSelect(string $name = 'spells', string $label = 'Spell List', bool $multiple = true): Select
    {
        return Select::make($name)
            ->label($label)
            ->multiple($multiple)
            ->searchable()
            ->reorderable()
            ->native(false)
            ->options(function (Get $get): array|Collection {
                $filters = json_decode($get('spell_filters'));
                $collection = Spell::all();

                if ($filters) {
                    $query = Spell::query();

                    foreach ($filters as $filter => $allowed) {
                        if ($allowed != []) {
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
                    }
                    $collection = $query->get();
                }

                return $collection->pluck('name', 'id');
            })
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
            );
    }

    /**
     * Get a TextInput for the system version, with a datalist of versions.
     */
    public static function getSystemVersionInput(): TextInput
    {
        return TextInput::make('system_version')
            ->label('System Version')
            ->datalist([
                '5e', '5.5e',
            ]);
    }

    /**
     * Get the array schema for a feature form.
     *
     * @param  array  $includedInputs  Optional. An array which includes all names of inputs that should be included. Defaults to all.
     */
    public static function getFeatureForm(array $includedInputs = ['name', 'level', 'snippet', 'description', 'modifiers', 'replaces', 'show_in_actions', 'has_active']): array
    {
        $inputs = [];
        $abilities = Ability::toArray();
        $saves = Ability::saveArray();
        $skills = Skill::all()->pluck('name', 'id')->toArray();
        $damageTypes = DamageType::all()->pluck('name', 'id')->toArray();
        $conditions = [/* conditions */];
        $tools = [/* tools */];
        $weaponTypes = [/* weapon types */];
        $weapons = [/* weapons */];

        foreach ($includedInputs as $input) {
            $inputs[] = match ($input) {
                'name' => TextInput::make($input)
                    ->live()
                    ->columnSpan(in_array('level', $includedInputs) ? 3 : 4),
                'level' => TextInput::make($input)
                    ->label('Level Gained')
                    ->default(1)
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(20)
                    ->live(),
                'snippet' => Textarea::make($input)
                    ->columnSpanFull(),
                'description' => Textarea::make($input)
                    ->columnSpanFull(),
                'modifiers' => Repeater::make($input)
                    ->schema([
                        FusedGroup::make([
                            Select::make('grant')
                                ->prefix('Grants:')
                                ->live()
                                ->options([
                                    'vul' => 'Vulnerability', 'res' => 'Resistance', 'imm' => 'Immunity',
                                    'adv' => 'Advantage', 'dadv' => 'Disadvantage',
                                    'abi' => 'Ability', 'save' => 'Saving Throw',
                                    'skill' => 'Skill',
                                    'prof' => 'Proficiency',
                                    // 'init' => 'Initiative',
                                    // 'ac' => 'Armor Class',
                                    'atk' => 'Attack Roll',
                                    'dmg' => 'Damage',
                                    'sen' => 'Sense',
                                    'lang' => 'Language',
                                    'spd' => 'Speed',
                                    'ign' => 'Ignore',
                                    'wm' => 'Weapon Mastery',
                                ])
                                ->searchable()
                                ->columnSpan(fn (?string $state): int => $state != null ? 1 : 3),
                            Select::make('on_type')
                                ->prefix('On Type:')
                                ->live()
                                ->options(fn (Get $get): array => match ($get('grant')) {
                                    default => [],
                                    'imm' => ['dmg' => 'Damage Type', 'cond' => 'Condition'],
                                    'adv', 'dadv' => [
                                        'fx' => 'Effect',
                                        'cond' => 'Condition',
                                        'abi' => 'Ability Check',
                                        'skill' => 'Skill',
                                        'save' => 'Saving Throw',
                                        'init' => 'Initiative',
                                        'atk' => 'Attack Roll',
                                    ],
                                    'prof' => [
                                        'Skills' => $skills,
                                        'Tools' => $tools,
                                        'Saving Throws' => $saves,
                                        'Weapon Types' => $weaponTypes,
                                        'Weapons' => $weapons,
                                    ]
                                })
                                ->searchable()
                                ->visible(fn (Get $get): bool => in_array($get('grant'), ['imm', 'adv', 'dadv', 'prof']))
                                ->columnSpan(fn (Get $get, ?string $state): int => in_array($get('grant'), ['imm']) || (in_array($get('grant'), ['adv', 'dadv']) && $state != 'init') || ($get('grant') == 'prof' && in_array($state, array_keys(array_merge($skills, $saves)))) ? 1 : 2),
                            Select::make('on')
                                ->prefix('On:')
                                ->options(fn (Get $get): array => match ($get('grant')) {
                                    default => [],
                                    'res', 'vul' => $damageTypes,
                                    'imm' => $get('type') == 'dmg' ? $damageTypes : ($get('type') == 'cond' ? $conditions : []),
                                    'adv', 'dadv' => match ($get('on_type')) {
                                        default => [],
                                        'fx' => [],
                                        'cond' => [],
                                        'abi' => [],
                                        'skill' => [],
                                        'save' => [],
                                        'atk' => [],
                                    },
                                    'abi', 'save', 'skill' => ['Abilities' => $abilities] + ($get('grant') != 'save' ? ['Skills' => $skills] : [])
                                })
                                ->searchable()
                                ->visible(fn (Get $get): bool => in_array($get('grant'), ['res', 'vul', 'imm', 'abi', 'save', 'skill']) || (in_array($get('grant'), ['adv', 'dadv']) && $get('on_type') != 'init'))
                                ->columnSpan(fn (Get $get): int => in_array($get('grant'), ['imm', 'abi', 'save', 'skill']) || (in_array($get('grant'), ['adv', 'dadv']) && $get('on_type') != 'init') ? 1 : 2),
                            Select::make('type')
                                ->prefix('Type:')
                                ->live()
                                ->options(fn (Get $get): array => match ($get('grant')) {
                                    'prof' => [
                                        'half' => 'Half-proficiency',
                                        'full' => 'Proficiency',
                                        'expt' => 'Expertise',
                                    ],
                                    'atk' => [
                                        'new' => 'New',
                                        'ranged' => 'Existing (Ranged)',
                                        'melee' => 'Existing (Melee)',
                                    ]
                                })
                                ->native(false)
                                ->visible(fn (Get $get): bool => in_array($get('grant'), ['atk']) || (in_array($get('grant'), ['prof']) && in_array($get('on_type'), array_keys(array_merge($skills, $saves))))),
                            TextInput::make('modifier')
                                ->prefix('Modifier:')
                                ->numeric()
                                ->visible(fn (Get $get): bool => in_array($get('grant'), ['abi', 'save', 'skill'])),
                            Select::make('ability')
                                ->prefix('Ability:')
                                ->options(Ability::toArray())
                                ->searchable()
                                ->visible(fn (Get $get): bool => $get('grant') == 'atk'),
                            TextInput::make('range')
                                ->prefix(fn (Get $get): string => $get('type') == 'ranged' ? 'Range' : 'Reach')
                                ->numeric()
                                ->visible(fn (Get $get): bool => in_array($get('type'), ['ranged', 'melee'])),
                            // Select::make('on')
                            //     ->prefix('On:')
                            //     ->options(fn (Get $get): array => match ($get('grant')) {
                            //         'res', 'imm', 'vul' => DamageType::all()->pluck('name', 'id')->toArray(),
                            //         'adv', 'dadv' => [
                            //             'eft' => 'Effect',
                            //             'cond' => 'Condition',
                            //             'abichk' => 'Ability Check',
                            //             'skill' => 'Skill',
                            //             'save' => 'Saving Throw',
                            //             'init' => 'Initiative',
                            //             'atk' => 'Attack rolls',
                            //         ],
                            //         'abi', 'save' => Ability::toArray(),
                            //         'skill' => Skill::all()->pluck('name', 'id')->toArray(),
                            //         'prof' => Skill::all()->pluck('name', 'id')->toArray() + Ability::saveArray(), // + tools
                            //         'ac' => [
                            //             'base' => 'Base Armor Class',
                            //             'static' => 'Static bonus',
                            //             'repldex' => 'Add Ability (Replace Dexterity)',
                            //             'addabi' => 'Add Ability (+ Dexterity)'
                            //         ],
                            //         'atk' => [], // specific weapon/general
                            //         'dmg' => [], // on specific type
                            //         'sen' => [
                            //             'dvis' => 'Darkvision',
                            //             'bsight' => 'Blindsight',
                            //             'tsense' => 'Tremorsense',
                            //             'tsight' => 'Truesight',
                            //         ],
                            //         'lang' => [], // languages
                            //         'spd' => [
                            //             'walk' => 'Walking',
                            //             'climb' => 'Climbing',
                            //             'fly' => 'Flying',
                            //             'burrow' => 'Burrowing',
                            //             'swim' => 'Swimming',
                            //         ],
                            //         'ign' => [], // speed reductions armor etc.
                            //         'wm' => [], // weapon masteries
                            //         default => [],
                            //     })
                            //     ->searchable()
                            //     ->columnSpan(fn (Get $get): int => in_array($get('grant'), ['res', 'imm', 'vul', 'wm']) ? 2 : 1),
                        ])
                            ->columns(3),
                    ])
                    ->addActionLabel('Add modifier')
                    ->columnSpanFull(),
                'replaces' => Checkbox::make($input),
                'show_in_actions' => Checkbox::make($input),
                'has_active' => Checkbox::make($input),
            };
        }

        return $inputs;
    }
}

/**
 * ✅ resistance | vulnerability => on (select) [damage types]
 * ✅ immunity => type (select) [damage types/conditions] => on (select) [damage types | conditions]
 * ✅ advantage | disadvantage => on type (select) [effects (magic sleep) & conditions & ability checks & skills & saves & initiative & attacks] => {! initiative} on (select)
 * ✅ ability | save | skill => on (select) [abilities/skills] => modifier (numeric textinput)
 * ✅ proficiency => on type (select) [skills & tools & saves & weapon types & weapon] => {! tools & ! weapon types & ! weapon} type (select) [half/full/exp.]
 * ❌ attack roll => type (select) [existing (ranged/melee)/new] => ability (select) => {ranged} range/{melee} reach (numeric textinput) => proficient (select) [proficient/obtainable/no]
 * ❌ damage => type (select) [existing (ranged/melee)/new] => damage type (select) [damage types] => amount (numeric textinput) => die type (select) [dice]
 * ❌ sense => type (select) [darkvision/blindsight/truesight/tremorsense] => range (numeric textinput) {+ ft}
 * ❌ language => language (select) [languages] => type (select) [read/speak/write] {multiple}
 * ❌ speed => type (select) [walk/climb/fly/burrow/swim] => modifier (numeric textinput) {+ ft}
 * ❌ ignore => ??
 * ❌ weapon mastery => on (select) [masteries]
 * ❌ resource (ki/sorcery/focus etc)

 * ❌ armor class, initiative? or bonus?
 */
