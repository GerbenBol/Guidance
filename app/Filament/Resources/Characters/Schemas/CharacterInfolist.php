<?php

namespace App\Filament\Resources\Characters\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CharacterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('race_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('background_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
