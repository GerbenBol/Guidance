<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Info')
                    ->icon(Heroicon::Identification)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->collection('avatar')
                            ->avatar()
                            ->circleCropper(),
                        Grid::make(1)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Username'),
                                TextInput::make('email')
                                    ->email()
                                    ->prefixIcon(Heroicon::Envelope, true),
                            ])
                            ->columnSpan(3)
                        // TextInput::make('password')
                        //     ->password()
                        //     ->prefixIcon(Heroicon::Key, true)
                        //     ->revealable(),
                    ])
                    ->columns(4)
                    ->collapsible()
                    ->columnSpanFull(),
                Section::make('Articles')
                    ->icon(Heroicon::Beaker)
                    ->schema([])
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}
