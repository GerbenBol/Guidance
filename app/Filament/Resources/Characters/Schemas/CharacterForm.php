<?php

namespace App\Filament\Resources\Characters\Schemas;

use App\Filament\Forms\Components\ClassFeature;
use App\Models\Character;
use App\Models\PlayerClass;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
                Wizard::make(function ($state): array {
                    foreach ($state['classes'] as $class) {
                        $primaryClass = PlayerClass::find($class['id']);
                        break;
                    }

                    return [
                        Step::make('Classes')
                            ->icon(Heroicon::OutlinedAcademicCap)
                            ->completedIcon(Heroicon::AcademicCap)
                            ->schema([
                                Repeater::make('classes')
                                    ->hiddenLabel()
                                    ->schema([
                                        Select::make('id')
                                            ->prefix('Class:')
                                            ->hiddenLabel()
                                            ->live()
                                            ->options(PlayerClass::getAllRecords('publicOrOwned'))
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                            ->searchable()
                                            ->columnSpan(6),
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
                                            ->columnSpan(4),
                                        Hidden::make('used_dice')
                                            ->default(0),
                                        TextInput::make('hp')
                                            ->prefix('HP:')
                                            ->hiddenLabel()
                                            ->numeric()
                                            ->default(0)
                                            ->live()
                                            ->suffixAction(Action::make('edit_hp')
                                                ->tooltip('Edit HP')
                                                ->icon(Heroicon::Pencil)
                                                ->badge(fn (Get $get): string => $get('level') - ($get('used_dice') ?? 0))
                                                ->badgeColor('primary')
                                                ->schema([
                                                    // add hit dice to class
                                                ]))
                                            ->columnSpan(2),
                                        Tabs::make()
                                            ->tabs([
                                                Tab::make('Features')
                                                    ->schema(function (Character $record, array $state, Get $get): array {
                                                        $class = PlayerClass::find($state['id']);

                                                        if (self::isPrimaryClass($record, $class->id)) {
                                                            $core = [
                                                                'name' => $class->name.' Core Traits Primary Class',
                                                                'description' => 'As a primary class the '.$class->name.' gains the following:<br>
                                                                    - <em>Saving Throw Proficiencies: '.implode(', ', array_map(fn ($item) => ucfirst($item), $class->class_info->save_prof)).'</em><br>',
                                                                'modifiers' => [],
                                                            ];

                                                            for ($i = 0; $i < $class->class_info->amount_of_skill_prof; $i++) {
                                                                $core['modifiers'][] = [
                                                                    'choice' => true,
                                                                    'grant' => 'skill',
                                                                    'on' => $class->class_info->skill_prof,
                                                                ];
                                                            }
                                                        } else {
                                                            $core = [
                                                                'name' => $class->name.' Core Traits Multiclass',
                                                                'description' => 'As a multiclass the '.$class->name.' gains the following:',
                                                                'modifiers' => [],
                                                            ];

                                                            for ($i = 0; $i < $class->class_info->amount_of_skill_prof; $i++) {
                                                                $core['modifiers'][] = [
                                                                    'choice' => true,
                                                                    'grant' => 'skill',
                                                                    'on' => $class->class_info->skill_prof,
                                                                ];
                                                            }
                                                        }
                                                        // add tool profs, armor profs, gaming sets to core
                                                        $core['level'] = 1;
                                                        $features = ['1' => [$core]];
                                                        $schema = [];
                                                        // dd($core);

                                                        foreach ($class->features as $feature) {
                                                            $features[$feature['level']][] = $feature;
                                                        }

                                                        foreach ($features as $level) {
                                                            foreach ($level as $feature) {
                                                                $schema[] = ClassFeature::make($feature['name'])
                                                                    ->hiddenLabel()
                                                                    ->visible(fn (Get $get): bool => $feature['level'] <= $get('level'))
                                                                    ->referesToFeature($feature)
                                                                    ->allModifiers($state['modifiers']);
                                                            }
                                                        }

                                                        return $schema;
                                                    }),
                                                Tab::make('Spells')
                                                    ->schema([])
                                                    ->visible(fn (Get $get): bool => PlayerClass::find($get('id'))?->spell_info?->can_cast_spells ?? false),
                                            ])
                                            ->columnSpanFull(),
                                    ])
                                    ->reorderable(false)
                                    ->collapsible()
                                    ->collapseAllAction(fn (Action $action): Action => $action->visible(false))
                                    ->expandAllAction(fn (Action $action): Action => $action->visible(false))
                                    ->deleteAction(fn (Action $action): Action => $action->requiresConfirmation())
                                    ->addActionLabel('Add class')
                                    ->itemLabel(fn (array $state): string => $state['level'].' '.PlayerClass::find($state['id'])?->name) // + ' / ' + subclass
                                    ->columns(12),
                            ]),
                        Step::make('Race')
                            ->icon(Heroicon::OutlinedAcademicCap)
                            ->completedIcon(Heroicon::AcademicCap)
                            ->schema([
                                // Select::make('id')
                                //     ->options([])
                                //     ->relationship('race', 'name')
                                //     ->searchable(),
                            ]),
                        Step::make('Background')
                            ->icon(Heroicon::OutlinedAcademicCap)
                            ->completedIcon(Heroicon::AcademicCap)
                            ->schema([
                                // Select::make('id')
                                //     ->options([])
                                //     ->relationship('background', 'name')
                                //     ->searchable(),
                            ]),
                        Step::make('Abilities')
                            ->icon(Heroicon::OutlinedAcademicCap)
                            ->completedIcon(Heroicon::AcademicCap)
                            ->schema([]),
                        Step::make('Equipment')
                            ->icon(Heroicon::OutlinedAcademicCap)
                            ->completedIcon(Heroicon::AcademicCap)
                            ->schema([]),
                    ];
                })
                    ->columnSpanFull()
                    ->skippable(),
            ]);
    }

    private static function isPrimaryClass(Character $character, int|string $class): bool
    {
        return ($character->classes()[0]->id ?? 0) == $class;
    }
}
