<?php

namespace App\Filament\Resources\Characters\Tables;

use App\Filament\Resources\Characters\Pages\CharacterSheet;
use App\Models\Character;
use App\Models\PlayerClass;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CharactersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('classes')
                    ->formatStateUsing(fn (array $state): string => PlayerClass::find($state['id'])->name)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('race.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('player.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('openSheet')
                    ->label('Sheet')
                    ->icon(Heroicon::BookOpen)
                    ->url(fn (Character $record) => CharacterSheet::getUrl(['record' => $record->id])),
                EditAction::make(),
            ])
            ->recordUrl(fn (Character $record) => CharacterSheet::getUrl(['record' => $record->id]));
    }
}
