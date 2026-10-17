<?php

namespace App\Filament\Resources\Characters\Tables;

use App\Filament\Resources\Characters\Pages\CharacterSheet;
use App\Models\Character;
use App\Models\PlayerClass;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class CharactersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('race.name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('classes')
                    ->numeric()
                    // ->formatStateUsing(fn ($state) => json_encode($state))
                    ->formatStateUsing(function ($state, Character $record): string {
                        // $classes = '';

                        // foreach ($record->classes() as $class) {
                        //     $classes .= $class->name.', ';
                        // }

                        // return substr($classes, 0, strlen($classes) - 2);
                        Log::info(implode(', ', $record->classes()->pluck('name')->toArray()));
                        return implode(', ', $record->classes()->pluck('name')->toArray());
                    })
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
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('openSheet')
                    ->label('Sheet')
                    ->icon(Heroicon::BookOpen)
                    ->url(fn ($record) => CharacterSheet::getUrl(['record' => $record->id])),
                EditAction::make(),
            ])
            ->recordUrl(fn (Character $record) => CharacterSheet::getUrl(['record' => $record->id]));
    }
}
