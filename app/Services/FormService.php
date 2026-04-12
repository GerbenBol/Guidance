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
    public static function getFeatureForm(array $includedInputs = ['name', 'lvl', 'snippet', 'description', 'modifiers', /* 'grants', */ 'replaces', 'show_in_actions', 'has_active']): array
    {
        $inputs = [];

        foreach ($includedInputs as $input) {
            $inputs[] = match ($input) {
                'name' => TextInput::make($input)
                    ->live()
                    ->columnSpan(in_array('lvl', $includedInputs) ? 3 : 4),
                'lvl' => TextInput::make($input)
                    ->label('Level Gained')
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
                                    'Resistance', 'Immunity', 'Vulnerability', // 0, 1, 2
                                    'Advantage', 'Disadvantage', // 3, 4
                                    'Ability', 'Saving Throw', // 5, 6
                                    'Skills', // 7
                                    'Proficiency', // 8
                                    'Attack Roll', // 9
                                    'Damage', // 10
                                    'Sense', // 11
                                    'Language', // 12
                                    'Speed', // 13
                                    'Ignore', // 14
                                    'Weapon Mastery', // 15
                                ])
                                ->searchable(),
                            Select::make('on')
                                ->prefix('On:')
                                ->options(fn (Get $get): array => match ($get('grant')) {
                                    0, 1, 2 => DamageType::all()->pluck('name', 'id')->toArray(),
                                    3, 4 => [
                                        'Effect',
                                        'Condition',
                                        'Ability Check',
                                        'Skill',
                                        'Saving Throw',
                                        'Initiative',
                                        'Attack rolls',
                                    ],
                                    5, 6 => Ability::toArray(),
                                    7 => Skill::all()->pluck('name', 'id')->toArray(),
                                    8 => Skill::all()->pluck('name', 'id')->toArray() + Ability::saveArray(), // + tools
                                    9 => [], // specific weapon/general
                                    10 => [], // on specific type
                                    11 => [
                                        'Darkvision',
                                        'Blindsight',
                                        'Tremorsense',
                                        'Truesight',
                                    ],
                                    12 => [], // languages
                                    13 => [
                                        'Walking',
                                        'Climbing',
                                        'Flying',
                                        'Burrowing',
                                        'Swimming',
                                    ],
                                    14 => [], // speed reductions armor etc.
                                    15 => [], // weapon masteries
                                    default => [],
                                })
                                ->searchable(),
                            // TextInput::make('modifier')
                            //     ->prefix('Modifier:'),
                        ])
                            ->columns(3),
                        Checkbox::make('replace'),
                    ])
                    ->addActionLabel('Add modifier')
                    ->columnSpanFull(),
                // 'grants' => Select::make($input)
                //     ->hiddenLabel()
                //     ->prefix('Grants:')
                //     ->options([
                //         'Resistance',
                //         'Immunity',
                //         'Vulnerability',
                //         'Bonus',
                //         'Advantage',
                //         'Disadvantage',
                //         'Sense',
                //         'Proficiency',
                //         'Language',
                //     ])
                //     ->searchable(),
                'replaces' => Checkbox::make($input),
                'show_in_actions' => Checkbox::make($input),
                'has_active' => Checkbox::make($input),
            };
        }

        return $inputs;
    }
}
