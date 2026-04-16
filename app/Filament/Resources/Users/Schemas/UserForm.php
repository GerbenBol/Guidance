<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
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
                        TextInput::make('name')
                            ->label('Username'),
                        TextInput::make('email')
                            ->email()
                            ->prefixIcon(Heroicon::Envelope, true),
                        TextInput::make('password')
                            ->password()
                            ->prefixIcon(Heroicon::Key, true)
                            ->revealable(),
                    ])
                    ->collapsible(),
                Section::make('Avatar')
                    ->icon(Heroicon::Photo)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->hiddenLabel()
                            ->collection('avatar')
                            ->image(),
                    ])
                    ->collapsible(),
                Section::make('Articles')
                    ->icon(Heroicon::Beaker)
                    ->schema([])
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}
