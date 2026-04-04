<?php

namespace App\Filament\Resources\Races\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                                TextInput::make('name')
                                    ->unique()
                                    ->required(),
                                Textarea::make('short_desc')
                                    ->label('Short Description'),
                                Textarea::make('description'),
                            ]),
                        Tab::make('Features')
                            ->schema([
                                Repeater::make('features')
                                    ->hiddenLabel()
                                    ->schema([])
                                    ->addActionLabel('Add feature'),
                            ]),
                    ]),
            ])
            ->columns(1);
    }
}
