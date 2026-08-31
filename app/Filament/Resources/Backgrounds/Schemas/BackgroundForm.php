<?php

namespace App\Filament\Resources\Backgrounds\Schemas;

use App\Models\Feat;
use App\Models\Skill;
use App\Services\FormService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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
                                Textarea::make('short_desc')
                                    ->label('Short Description'),
                                Textarea::make('description'),
                            ]),
                        Tab::make('Details')
                            ->schema([
                                Section::make('Proficiencies')
                                    ->schema([
                                        Repeater::make('profs')
                                            ->hiddenLabel()
                                            ->schema([
                                                FusedGroup::make([
                                                    Select::make('granted')
                                                        ->hiddenLabel()
                                                        ->live()
                                                        ->native(false)
                                                        ->default(0)
                                                        ->options(['Granted', 'Choose One']),
                                                    Select::make('type')
                                                        ->hiddenLabel()
                                                        ->prefix('Type')
                                                        ->options([
                                                            'skill' => 'Skill',
                                                            'tool' => 'Tool',
                                                            'lang' => 'Language',
                                                        ])
                                                        ->native(false),
                                                    Select::make('options')
                                                        ->hiddenLabel()
                                                        ->prefix(fn (Get $get): string => ! $get('granted') ? 'Option' : 'Options')
                                                        ->multiple(fn (Get $get): bool => $get('granted') == 1 ?? false)
                                                        ->searchable()
                                                        ->options(fn (Get $get): array => match ($get('type')) {
                                                            'skill' => Skill::all()->pluck('name', 'id')->toArray(),
                                                            'tool' => [],
                                                            'lang' => [],
                                                            default => [],
                                                        })
                                                        ->columnSpan(2),
                                                ])
                                                    ->columns(4),
                                            ]),
                                    ])
                                    ->secondary()
                                    ->collapsed(),
                                Section::make('Feats')
                                    ->schema([
                                        Repeater::make('feats')
                                            ->hiddenLabel()
                                            ->schema([
                                                FusedGroup::make([
                                                    Select::make('granted')
                                                        ->live()
                                                        ->native(false)
                                                        ->default(0)
                                                        ->options(['Granted', 'Choose One']),
                                                    Select::make('options')
                                                        ->prefix(fn (Get $get): string => ! $get('granted') ? 'Option' : 'Options')
                                                        ->multiple(fn (Get $get): bool => $get('granted') == 1 ?? false)
                                                        ->searchable()
                                                        ->options(Feat::getAllRecords('publicOrOwned')),
                                                ])
                                                    ->columns(2),
                                            ]),
                                    ])
                                    ->secondary()
                                    ->collapsed(),
                                Section::make('Equipment')
                                    ->schema([
                                        FormService::getEquipmentRepeater(),
                                    ])
                                    ->secondary()
                                    ->collapsed(),
                            ]),
                    ]),
            ])
            ->columns(1);
    }
}
