<?php

namespace App\Filament\Resources\Spells\Schemas;

use App\Enums\Dice;
use App\Models\DamageType;
use App\Models\School;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
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
