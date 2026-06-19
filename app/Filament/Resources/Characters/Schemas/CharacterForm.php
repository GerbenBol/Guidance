<?php

namespace App\Filament\Resources\Characters\Schemas;

use App\Filament\Forms\Components\ClassFeature;
use App\Models\PlayerClass;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

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
                                        // save prof, skill prof
                                        Text::make(new HtmlString('Saving Throw Proficiencies: <br>'.implode(', ', array_map(fn ($item) => ucfirst($item), $primaryClass->class_info->save_prof))))
                                            ->tooltip($primaryClass->name)
                                            ->columnSpan(3),
                                        Tabs::make()
                                            ->tabs([
                                                Tab::make('Features')
                                                    ->schema(function (array $state, Get $get): array {
                                                        $schema = [];
                                                        $class = PlayerClass::find($state['id']);

                                                        foreach ($class->features as $feature) {
                                                            // dd($feature);
                                                            $schema[] = ClassFeature::make($feature['name'])
                                                                ->hiddenLabel()
                                                                ->visible(fn (Get $get): bool => $feature['level'] <= $get('level'))
                                                                ->referesToFeature($feature);
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
                    ];
                })
                    ->columnSpanFull()
                    ->skippable(),
            ]);
    }
}
