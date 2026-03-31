<?php

namespace App\Filament\Resources\Classes\Schemas;

use App\Enums\Abilities;
use App\Services\FeatureService;
use App\Services\FormService;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class ClassForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(function (): array {
                $operation = explode('/', request()->url());
                $operation = $operation[count($operation) - 1];

                $general = [
                    TextInput::make('name')
                        ->afterLabel(FormService::makeHintIcon('The name of the class'))
                        ->required()
                        ->columnSpanFull(),
                    Textarea::make('short_desc')
                        ->label('Short Description')
                        ->afterLabel(FormService::makeHintIcon('A short description, which is shown in the character builder and in the class overview')),
                    Textarea::make('description')
                        ->afterLabel(FormService::makeHintIcon('The full description')),
                    Checkbox::make('can_cast_spells')
                        ->live()
                        ->default(true),
                ];

                if ($operation == 'create') {
                    return $general;
                } else {
                    return [
                        Wizard::make([
                            Step::make('General')
                                ->schema($general),
                            Step::make('Features')
                                ->schema([
                                    Repeater::make('features')
                                        ->hiddenLabel()
                                        ->schema(FeatureService::singleFormItem())
                                        ->reorderable(false),
                                ]),
                            Step::make('Spellcasting')
                                ->schema([
                                    Section::make('Spell Slot Table')
                                        ->schema(function (): array {
                                            $levels = [];

                                            for ($lvl = 1; $lvl <= 20; $lvl++) {
                                                $inputs = [];

                                                for ($slot = 0; $slot <= 9; $slot++) {
                                                    $inputs[] = TextInput::make('lvl'.$lvl.'slot'.$slot)
                                                        ->prefix($slot == 0 ? 'Level '.$lvl : '')
                                                        ->numeric();
                                                }

                                                $levels[] = FusedGroup::make($inputs)
                                                    ->columns(10);
                                            }

                                            return $levels;
                                        })
                                        ->secondary()
                                    // ->collapsed()
                                    ,
                                    Select::make('spell_ability')
                                        ->label('Spellcasting Ability')
                                        ->options(Abilities::class)
                                        ->native(false),
                                ])
                                ->columns(2),
                            // ->visible(fn (Get $get): bool => $get('can_cast_spells')),
                        ])
                            ->skippable()
                            ->columnSpanFull()
                            ->startOnStep(2),
                    ];
                }
            });
    }
}
