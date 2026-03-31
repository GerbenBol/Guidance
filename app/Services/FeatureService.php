<?php

namespace App\Services;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;

class FeatureService
{
    public static function singleFormItem(): array
    {
        return [
            Grid::make(4)
                ->schema([
                    TextInput::make('name')
                        ->columnSpan(3),
                    TextInput::make('level')
                        ->numeric()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Set $set): string => $set('level', $state < 1 ? 1 : ($state > 20 ? 20 : $state))),
                    Textarea::make('short_desc')
                        ->label('Short Description')
                        ->columnSpanFull(),
                    Textarea::make('description')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
