<?php

namespace App\Filament\Resources\Characters\Schemas;

use App\Models\Character;
use App\Models\Sheet;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CharacterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(function (Character $record) {
                dd($record->sheet->background);
                return [
                    TextEntry::make('name'),
                    TextEntry::make('race_id')
                        ->numeric()
                        ->placeholder('-')
                        ->action(
                            Action::make('showRace')
                                ->slideOver()
                                ->schema([
                                    TextEntry::make('race.name'),
                                ])
                        ),
                    TextEntry::make('background_id')
                        ->numeric()
                        ->placeholder('-'),
                    TextEntry::make('created_at')
                        ->dateTime()
                        ->placeholder('-'),
                    TextEntry::make('updated_at')
                        ->dateTime()
                        ->placeholder('-'),
                ];
            });
    }
}
