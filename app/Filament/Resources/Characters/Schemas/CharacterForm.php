<?php

namespace App\Filament\Resources\Characters\Schemas;

use App\Models\PlayerClass;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class CharacterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('player_id')
                    ->default(Auth::user()->id),
                TextInput::make('name')
                    ->inlineLabel()
                    ->columnSpanFull()
                    ->required(),
                Wizard::make([
                    Step::make('Classes')
                        ->schema([
                            Repeater::make('classes')
                                ->hiddenLabel()
                                ->schema([
                                    Select::make('id')
                                        ->prefix('Class:')
                                        ->hiddenLabel()
                                        ->live()
                                        ->options(PlayerClass::getAllRecords('publicOrOwned'))
                                        ->searchable()
                                        ->columnSpan(8),
                                    Select::make('level')
                                        ->prefix('Levels:')
                                        ->hiddenLabel()
                                        ->searchable()
                                        ->live()
                                        ->options(function (Get $get): array {
                                            $levels = [];

                                            for ($i = 1; $i <= (PlayerClass::find($get('id'))?->max_levels ?? 20); $i++) {
                                                $levels[$i] = 'Level '.$i;
                                            }

                                            return $levels;
                                        })
                                        ->columnSpan(2),
                                    Hidden::make('used_dice')
                                        ->default(0),
                                    TextInput::make('hp')
                                        ->prefix('HP:')
                                        ->hiddenLabel()
                                        ->numeric()
                                        ->default(0)
                                        ->suffixAction(Action::make('edit_hp')
                                            ->tooltip('Edit HP')
                                            ->icon(Heroicon::Pencil)
                                            ->badge(fn (Get $get): string => $get('level') - ($get('used_dice') ?? 0))
                                            ->badgeColor('primary')
                                            ->schema([
                                                // add hit dice to class
                                            ]))
                                        ->columnSpan(2),
                                ])
                                ->reorderable(false)
                                ->deleteAction(fn (Action $action): Action => $action->requiresConfirmation())
                                ->addActionLabel('Add class')
                                ->columns(12),
                        ]),
                    Step::make('Race')
                        ->schema([
                            // Select::make('id')
                            //     ->options([])
                            //     ->relationship('race', 'name')
                            //     ->searchable(),
                        ]),
                    Step::make('Background')
                        ->schema([
                            // Select::make('id')
                            //     ->options([])
                            //     ->relationship('background', 'name')
                            //     ->searchable(),
                        ]),
                    Step::make('Abilities')
                        ->schema([]),
                    Step::make('Equipment')
                        ->schema([]),
                ])
                    ->columnSpanFull()
                    ->skippable(),
            ]);
    }
}
