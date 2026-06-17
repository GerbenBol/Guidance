<?php

namespace App\Filament\Resources\Characters\Schemas;

use App\Models\PlayerClass;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CharacterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('player_id')
                    ->default(Auth::user()->id),
                TextInput::make('name')
                    ->inlineLabel()
                    ->columnSpanFull()
                    ->required(),
                Wizard::make([
                    Step::make('Classes')
                        ->schema([
                            Repeater::make('classes')
                                ->hiddenLabel()
                                ->schema([
                                    Select::make('id')
                                        ->label('Class')
                                        ->inlineLabel()
                                        ->live()
                                        ->options(PlayerClass::getAllRecords('publicOrOwned'))
                                        ->searchable()
                                        ->columnSpan(8),
                                    Select::make('level')
                                        ->label('Levels')
                                        ->inlineLabel()
                                        ->options([])
                                ])
                                ->columns(12),
                        ]),
                    Step::make('Race')
                        ->schema([
                            // Select::make('id')
                            //     ->options([])
                            //     ->relationship('race', 'name')
                            //     ->searchable(),
                        ]),
                    Step::make('Background')
                        ->schema([
                            // Select::make('id')
                            //     ->options([])
                            //     ->relationship('background', 'name')
                            //     ->searchable(),
                        ]),
                    Step::make('Abilities')
                        ->schema([]),
                    Step::make('Equipment')
                        ->schema([]),
                ])
                    ->columnSpanFull()
                    ->skippable(),
            ]);
    }
}
