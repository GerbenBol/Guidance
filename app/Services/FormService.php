<?php

namespace App\Services;

use App\Models\School;
use App\Models\Spell;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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

    public static function getSystemVersionInput(): TextInput
    {
        return TextInput::make('system_version')
            ->label('System Version')
            ->datalist([
                '5e', '5.5e',
            ]);
    }
}
