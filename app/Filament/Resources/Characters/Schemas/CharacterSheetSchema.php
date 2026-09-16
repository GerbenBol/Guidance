<?php

namespace App\Filament\Resources\Characters\Schemas;

use App\Models\Character;
use App\Models\Sheet;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
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
                // dd($record->sheet->abilities);

                return [
                    Section::make()
                        ->schema([
                            Actions::make([
                                Action::make('manage')
                                    ->hiddenLabel()
                                    ->tooltip('Manage Character')
                                    ->icon(Heroicon::Cog)
                                    ->url('edit'),
                            ])
                                ->columnSpan(2),
                            TextEntry::make('name')
                                ->hiddenLabel()
                                ->formatStateUsing(fn (string $state): string => '<b>'.$state.'</b><br>'.
                                    '<em>'.$record->sheet->race->name.' - '.
                                    implode(' / ', $record->sheet->classes()->pluck('name')->toArray()).'</em>'
                                )
                                ->html()
                                ->columnSpan(9),
                        ])
                        ->columns(12)
                        ->columnSpan(4),
                    self::emptySpace()
                        ->columnSpan(2),
                    Section::make()
                        ->schema([
                            TextInput::make('hp')
                                ->hiddenLabel()
                                ->prefixAction(
                                    Action::make('heal')
                                        ->hiddenLabel()
                                        ->icon(Heroicon::Plus)
                                        ->action(fn ($state) => $record->sheet->hp = dd($record->sheet->hp + $state))
                                ),
                        ])
                        ->columnSpan(6),
                    Section::make()
                        ->schema([
                            RepeatableEntry::make('abilities')
                                ->hiddenLabel()
                                ->schema([
                                    TextEntry::make('ability')
                                        ->hiddenLabel()
                                        ->alignCenter(),
                                ])
                                ->getStateUsing(fn () => $record->sheet->abilities)
                                ->grid(6),
                        ])
                        ->columnSpan(8),
                ];
            })
            ->columns(12);
    }

    private static function emptySpace(): TextEntry
    {
        return TextEntry::make('empty')
            ->hiddenLabel();
    }
}
