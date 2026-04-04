<?php

namespace App\Filament\Resources\Characters\Schemas;

use Filament\Forms\Components\Hidden;
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
                            Select::make('class_id')
                                // ->relationship('classes', 'name')
                                ->native(),
                        ]),
                    Step::make('Race')
                        ->schema([
                            Select::make('race_id')
                                ->options([])
                                // ->relationship('race', 'name')
                                ->native(false),
                        ]),
                    Step::make('Background')
                        ->schema([
                            Select::make('background_id')
                                ->options([])
                                // ->relationship('background', 'name')
                                ->native(false),
                        ]),
                    Step::make('Abilities')
                        ->schema([]),
                ])
                    ->columnSpanFull()
                    ->skippable(),
            ]);
    }
}
