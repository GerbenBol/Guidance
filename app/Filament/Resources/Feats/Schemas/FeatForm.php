<?php

namespace App\Filament\Resources\Feats\Schemas;

use App\Enums\Ability;
use App\Models\Background;
use App\Models\PlayerClass;
use App\Models\Race;
use App\Models\Skill;
use App\Services\FormService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class FeatForm
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
                                TextInput::make('name')
                                    ->columnSpan(3),
                                FormService::getSystemVersionInput(),
                                Textarea::make('description')
                                    ->columnSpanFull(),
                                Textarea::make('short_desc')
                                    ->label('Short Description')
                                    ->columnSpanFull(),
                            ])
                            ->columns(4),
                        Tab::make('Features')
                            ->schema([FormService::getFeatureRepeater(['name', 'snippet', 'description', 'modifiers', 'replaces', 'show_in_actions', 'has_active'])]),
                        Tab::make('Requirements')
                            ->schema([
                                Repeater::make('requirements')
                                    ->hiddenLabel()
                                    ->schema([
                                        FusedGroup::make([
                                            Select::make('type')
                                                ->prefix('Type:')
                                                ->options([
                                                    'race' => 'Race',
                                                    'class' => 'Class',
                                                    'cha-lvl' => 'Character Level',
                                                    'cla-lvl' => 'Class Level',
                                                    'backgr' => 'Background',
                                                    'ability' => 'Ability',
                                                    'prof' => 'Proficiency',
                                                ])
                                                ->live()
                                                ->searchable()
                                                ->columnSpan(4),
                                            Select::make('choice')
                                                ->visible(fn (Get $get): bool => ! in_array($get('type'), ['cha-lvl', 'cla-lvl']))
                                                ->options(fn (Get $get): array => match ($get('type')) {
                                                    'race' => Race::getAllRecords('publicOrOwned'),
                                                    'class' => PlayerClass::getAllRecords('publicOrOwned'),
                                                    'backgr' => Background::getAllRecords('publicOrOwned'),
                                                    'ability' => Ability::toArray(),
                                                    'prof' => array_merge(
                                                        Skill::getAllRecords('public'),
                                                        // tools, languages, weapons
                                                    ),
                                                    default => [],
                                                })
                                                ->searchable()
                                                ->columnSpan(8),
                                            Select::make('min_or_max')
                                                ->visible(fn (Get $get): bool => in_array($get('type'), ['cha-lvl', 'cla-lvl']))
                                                ->options([
                                                    'min' => 'Minimum',
                                                    'max' => 'Maximum',
                                                ])
                                                ->default('min')
                                                ->native(false)
                                                ->columnSpan(fn (Get $get): int => $get('type') == 'cla-lvl' ? 2 : 4),
                                            Select::make('class')
                                                ->prefix('Class:')
                                                ->visible(fn (Get $get): bool => $get('type') == 'cla-lvl')
                                                ->options(PlayerClass::getAllRecords('publicOrOwned'))
                                                ->searchable()
                                                ->columnSpan(3),
                                            TextInput::make('choice')
                                                ->prefix('Level:')
                                                ->numeric()
                                                ->visible(fn (Get $get): bool => in_array($get('type'), ['cha-lvl', 'cla-lvl']))
                                                ->columnSpan(fn (Get $get): int => $get('type') == 'cla-lvl' ? 3 : 4),
                                        ])
                                            ->columns(12),
                                    ])
                                    ->addActionLabel('Add requirement'),
                            ]),
                    ]),
            ])
            ->columns(1);
    }
}
