<?php

namespace App\Filament\Resources\Races\Schemas;

use App\Services\FormService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class RaceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(Auth::user()->id),
                Tabs::make()
                    ->tabs([
                        Tab::make('General')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('name')
                                            ->columnSpan(3)
                                            ->required(),
                                        FormService::getSystemVersionInput(),
                                    ]),
                                Textarea::make('short_desc')
                                    ->label('Short Description'),
                                Textarea::make('description'),
                            ]),
                        Tab::make('Features')
                            ->schema([FormService::getFeatureRepeater()]),
                    ]),
            ])
            ->columns(1);
    }
}
