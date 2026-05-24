<?php

namespace App\Filament\Resources\Backgrounds\Schemas;

use App\Services\FormService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class BackgroundForm
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
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('name')
                                            ->columnSpan(3)
                                            ->required(),
                                        FormService::getSystemVersionInput(),
                                    ]),
                                Textarea::make('description'),
                            ]),
                        Tab::make('Details')
                            ->schema([
                                // Repeater::make('profs')
                                //     ->label('Proficiencies')
                                //     ->schema([
                                //         Grid::make(4)
                                //             ->schema([
                                //                 Radio::make('granted')
                                //                     ->hiddenLabel()
                                //                     ->inline()
                                //                     ->live()
                                //                     ->default(true)
                                //                     ->boolean('Granted', 'Choice'),
                                //                 Select::make('type')
                                //                     ->hiddenLabel()
                                //                     ->options([
                                //                         'skill' => 'Skill',
                                //                         'tool' => 'Tool',
                                //                         'lang' => 'Language',
                                //                     ])
                                //                     ->native(false),
                                //                 Select::make('options')
                                //                     ->hiddenLabel()
                                //                     ->prefix(fn (Get $get): string => $get('granted') ? 'Option' : 'Options')
                                //                     ->multiple(fn (Get $get): bool => !$get('granted') ?? false)
                                //                     ->searchable()
                                //                     ->options([])
                                //                     ->columnSpan(2)
                                //             ]),
                                //     ]),
                                Repeater::make('profs')
                                    ->label('Proficiencies')
                                    ->schema([
                                        FusedGroup::make([
                                            Select::make('granted')
                                                ->hiddenLabel()
                                                ->live()
                                                ->native(false)
                                                ->default(1)
                                                ->options(['Choice', 'Granted'])
                                                ->columnSpan(3),
                                            Select::make('type')
                                                ->hiddenLabel()
                                                ->options([
                                                    'skill' => 'Skill',
                                                    'tool' => 'Tool',
                                                    'lang' => 'Language',
                                                ])
                                                ->native(false)
                                                ->columnSpan(3),
                                            Select::make('options')
                                                ->hiddenLabel()
                                                // ->prefix(fn (Get $get): string => $get('granted') ? 'Option' : 'Options')
                                                // ->multiple(fn (Get $get): bool => !$get('granted') ?? false)
                                                ->searchable()
                                                ->options([])
                                                ->columnSpan(6),
                                            // Select::make('granted')
                                        ])
                                            ->columns(12),
                                    ]),
                                Repeater::make('feats')
                                    ->schema([]),
                                Repeater::make('equipment')
                                    ->schema([
                                        Repeater::make('items')
                                            ->hiddenLabel()
                                            ->schema([]),
                                    ])
                                    ->itemLabel(fn (int $index): string => 'Option '.chr(65 + $index)),
                            ]),
                    ])
                    ->activeTab(2),
            ])
            ->columns(1);
    }
}
