<?php

namespace App\Filament\Resources\Characters\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class CharacterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->inlineLabel()
                    ->columnSpanFull()
                    ->required(),
                Wizard::make([
                    Step::make('Class')
                        ->schema([
                            //
                        ]),
                    Step::make('Race')
                        ->schema([
                            Select::make('race_id')
                                ->relationship('race', 'name')
                                ->native(false),
                        ]),
                    Step::make('Background')
                        ->schema([
                            Select::make('background_id')
                                ->relationship('background', 'name')
                                ->native(false),
                        ]),
                    Step::make('Abilities')
                        ->schema([]),
                ])
                ->columnSpanFull()
                ->skippable()
            ]);
    }
}
