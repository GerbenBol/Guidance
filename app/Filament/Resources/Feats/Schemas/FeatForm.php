<?php

namespace App\Filament\Resources\Feats\Schemas;

use App\Services\FormService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
                                TextInput::make('name'),
                                FormService::getSystemVersionInput(),
                                Textarea::make('description'),
                                Textarea::make('short_desc'),
                            ]),
                        Tab::make('Features')
                            ->schema([
                                Repeater::make('features')
                                    ->hiddenLabel()
                                    ->schema(FormService::getFeatureForm())
                                    ->columns(4)
                                    ->reorderable()
                                    ->collapsible()
                                    ->addActionLabel('Add feature')
                                    ->itemLabel(fn (array $state): string => trim(($state['name'] ?? '').' '.($state['level'] != null ? '(Level '.$state['level'].' feature)' : ''))),
                            ]),
                        Tab::make('Requirements')
                            ->schema([
                                Repeater::make('requirements')
                                    ->hiddenLabel()
                                    ->schema([]),
                            ]),
                    ]),
            ])
            ->columns(1);
    }
}
