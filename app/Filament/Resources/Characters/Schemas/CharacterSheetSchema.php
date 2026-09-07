<?php

namespace App\Filament\Resources\Characters\Schemas;

use App\Models\Character;
use App\Models\Sheet;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CharacterSheetSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(function (Character $record) {
                // dd($record->sheet);

                if (! $record->sheet) {
                    Sheet::create([
                        'character_id' => $record->id,
                    ])->generate();
                } elseif (! $record->sheet->isUpToDate()) {
                    $record->sheet->generate();
                }

                return [
                    Grid::make(12)
                        ->schema([
                            Actions::make([
                                Action::make('manage')
                                    ->hiddenLabel()
                                    ->icon(Heroicon::Cog),
                            ])
                                ->columnSpan(2),
                            TextEntry::make('name')
                                ->hiddenLabel()
                                ->formatStateUsing(fn (string $state): string => '<b>'.$state.'</b><br>'.
                                    '<em>'.implode(' / ', $record->sheet->classes()->pluck('name')->toArray()).'</em>'
                                )
                                ->html()
                                ->columnSpan(10),
                        ])
                        ->columnSpan(3),
                    // TextEntry::make('name'),
                    // TextEntry::make('race_id')
                    //     ->numeric()
                    //     ->placeholder('-')
                    //     ->action(
                    //         Action::make('showRace')
                    //             ->slideOver()
                    //             ->schema([
                    //                 TextEntry::make('race.name'),
                    //             ])
                    //     ),
                    // TextEntry::make('background_id')
                    //     ->numeric()
                    //     ->placeholder('-'),
                    // TextEntry::make('created_at')
                    //     ->dateTime()
                    //     ->placeholder('-'),
                    // TextEntry::make('updated_at')
                    //     ->dateTime()
                    //     ->placeholder('-'),
                ];
            })
            ->columns(12);
    }
}
