<?php

namespace App\Filament\Resources\Spells\Schemas;

use App\Enums\Dice;
use App\Models\DamageType;
use App\Models\School;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SpellForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([
                    TextInput::make('name')
                        ->unique()
                        ->required(),
                    Select::make('level')
                        ->options([
                            'Cantrip', 1, 2, 3,
                            4, 5, 6, 7, 8, 9,
                        ])
                        ->native(false),
                    Select::make('school_id')
                        ->label('Magic School')
                        ->options(School::all()->pluck('name', 'id'))
                        ->searchable()
                        ->native(false),
                ]),
                Textarea::make('short_desc')
                    ->label('Short Description'),
                Textarea::make('description'),
                Grid::make(2)
                    ->schema([
                        Fieldset::make('Components')
                            ->schema([
                                CheckboxList::make('components')
                                    ->options([
                                        'v' => 'Verbal',
                                        's' => 'Somatic',
                                        'm' => 'Material',
                                    ])
                                    ->live(),
                                Textarea::make('materials')
                                    ->rows(4)
                                    ->autosize()
                                    ->disabled(fn (Get $get) => ! in_array('m', $get('components'))),
                            ]),
                        Fieldset::make('Range/ Area')
                            ->schema([
                                TextInput::make('range')
                                    ->visible(fn (Get $get): bool => in_array($get('range_type'), ['feet', 'mile', 'inch'])),
                                Select::make('range_type')
                                    ->options([
                                        'self' => 'Self',
                                        'touch' => 'Touch',
                                        'inch' => 'Inch',
                                        'feet' => 'Feet',
                                        'mile' => 'Mile',
                                    ])
                                    ->native(false)
                                    ->columnSpan(fn (Get $get): int => in_array($get('range_type'), ['feet', 'mile', 'inch']) ? 1 : 2)
                                    ->live(),
                                TextInput::make('area')
                                    ->suffix('ft.'),
                                Select::make('area_type')
                                    ->options([
                                        'cube' => 'Cube',
                                        'line' => 'Line',
                                        'cylinder' => 'Cylinder',
                                        'emanation' => 'Emanation',
                                        'sphere' => 'Sphere',
                                    ])
                                    ->native(false),
                            ]),
                        Fieldset::make('Casting Time')
                            ->schema([]),
                        Fieldset::make('Duration')
                            ->schema([]),
                    ]),
                Section::make('Effect')
                    ->schema([
                        Repeater::make('effect')
                            ->hiddenLabel()
                            ->schema([
                                Select::make('type')
                                    ->label('Type')
                                    ->options(['Health', 'Temporary HP'] + DamageType::all()->pluck('name', 'id')->toArray())
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->searchable()
                                    ->native(false),
                                TextInput::make('amount')
                                    ->label('Amount of Dice')
                                    ->default(0)
                                    ->numeric(),
                                Select::make('dice')
                                    ->label('Dice Type')
                                    ->options(Dice::toArray() + ['straight' => 'No Roll'])
                                    ->searchable()
                                    ->native(false),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add damage'),
                    ])
                    ->collapsible(),
                Section::make('Scaling')
                    ->schema([
                        Textarea::make('scale_desc')
                            ->label('Description'),
                        Repeater::make('scaling')
                            ->hiddenLabel()
                            ->schema([
                                Select::make('scale_moment')
                                    ->label('Moment when scale happens')
                                    ->options([
                                        'Per Character Level',
                                        'Per Spell Level',
                                        'Certain Character Levels',
                                        'Certain Spell Levels',
                                        'Once',
                                    ])
                                    ->native(false),
                                Select::make('type')
                                    ->label('Type')
                                    ->options(['Health', 'Temporary HP'] + DamageType::all()->pluck('name', 'id')->toArray())
                                    ->searchable()
                                    ->native(false),
                                TextInput::make('amount')
                                    ->label('Amount of Dice')
                                    ->default(0)
                                    ->numeric(),
                                Select::make('dice')
                                    ->label('Dice Type')
                                    ->options(Dice::toArray() + ['straight' => 'No Roll'])
                                    ->searchable()
                                    ->native(false),
                            ])
                            ->columns(4)
                            ->addActionLabel('Add scaling'),
                    ])
                    ->collapsible(),
            ])
            ->columns(1);
    }
}
