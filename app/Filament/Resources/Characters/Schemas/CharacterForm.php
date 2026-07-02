<?php

namespace App\Filament\Resources\Characters\Schemas;

use App\Filament\Forms\Components\ClassFeature;
use App\Models\Character;
use App\Models\PlayerClass;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class CharacterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(fn (Character $record): array => [
                Hidden::make('player_id')
                    ->default(Auth::user()->id),
                TextInput::make('name')
                    ->hiddenLabel()
                    ->prefix('Name:')
                    ->columnSpan(10)
                    ->required(),
                TextEntry::make('hp')
                    ->hiddenLabel()
                    ->default(0)
                    ->formatStateUsing(fn () => 'Total HP: '.collect($record->classes)->pluck('total_hp')->sum())
                    ->size(TextSize::Large)
                    ->alignment(Alignment::Center)
                    ->columnSpan(2)
                    ->suffixAction(
                        Action::make('checkHP')
                            ->tooltip('Check Total HP Calculations')
                            ->icon(Heroicon::QuestionMarkCircle)
                            ->modalHeading('Total HP Check')
                            ->schema([
                                TextEntry::make('total')
                                    ->hiddenLabel()
                                    ->state('Total HP: '.collect($record->classes)->pluck('total_hp')->sum())
                                    ->size(TextSize::Large)
                                    ->alignment(Alignment::Center),
                                self::getDivider(),
                                Grid::make('3')
                                    ->schema([
                                        // Checkbox::make('fixed_hp'),
                                        Select::make('use_fixed_hp')
                                            ->hiddenLabel()
                                            ->prefix('Fixed HP:')
                                            ->boolean()
                                            ->default(false),
                                        TextInput::make('hp_modifier')
                                            ->hiddenLabel()
                                            ->prefix('Modifier:')
                                            ->numeric()
                                            ->placeholder('-'),
                                        TextInput::make('overwrite_hp')
                                            ->hiddenLabel()
                                            ->prefix('Overwrite:')
                                            ->numeric()
                                            ->placeholder('-'),
                                    ]),
                                self::getDivider(),
                                TextEntry::make('current_calculations')
                                    ->hiddenLabel()
                                    ->state(new HtmlString('Current HP calculations:<br>'))
                                    ->formatStateUsing(function ($state, Character $record): string {
                                        foreach ($record->classes as $class) {
                                            $c = PlayerClass::find($class['id']);
                                            // dd($class);
                                            $state .= '- '.$c->name.' ('.$c->hit_die.'): '.$class['total_hp'].' [';
                                            $state .= '<br>';
                                        }

                                        return $state;
                                    })
                                    ->html(),
                                self::getDivider(),
                                TextEntry::make('explanation')
                                    ->hiddenLabel()
                                    ->state(new HtmlString(
                                        'Total HP is calculated in the following way:<br>'.
                                        '<ul>
                                            <li>- All classes\' rolled/ manual values of Hit Die * amount of levels in that class;</li>
                                            <li>- Character\' Constitution modifier * character\'s total level;</li>
                                            <li>- Any other HP multipliers or modifiers.</li>
                                        </ul><br>'.
                                        'The sum of the above values is the character\'s total HP. This can also be overwritten.'
                                    )),
                            ])
                    ),
                Wizard::make(function (Get $get): array {
                    if ($get('classes')) {
                        foreach ($get('classes') as $class) {
                            $primaryClass = PlayerClass::find($class['id']);
                            break;
                        }
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
                                            ->default(1)
                                            ->columnSpan(4),
                                        Hidden::make('used_dice')
                                            ->default(0),
                                        Hidden::make('hp'),
                                        TextInput::make('total_hp')
                                            ->prefix('HP:')
                                            ->hiddenLabel()
                                            ->numeric()
                                            ->default(0)
                                            ->live()
                                            ->suffixAction(Action::make('edit_hp')
                                                ->tooltip('Edit HP')
                                                ->icon(Heroicon::WrenchScrewdriver)
                                                ->badge(fn (Get $get): string => $get('level') - ($get('used_dice') ?? 0))
                                                ->badgeColor('primary')
                                                ->modalHeading('Edit HP')
                                                ->modalSubmitActionLabel('Save')
                                                ->schema(function (Get $get): array {
                                                    $schema = [
                                                        TextEntry::make('hp')
                                                            ->hiddenLabel()
                                                            ->state('Current class HP: '.collect($get('hp'))->sum())
                                                            ->size(TextSize::Large)
                                                            ->alignment(Alignment::Center),
                                                        self::getDivider(),
                                                        Grid::make(4)
                                                            ->schema([
                                                                TextEntry::make('nothing')
                                                                    ->hiddenLabel(),
                                                                TextEntry::make('die_type')
                                                                    ->hiddenLabel()
                                                                    ->state('Hit Die: '.PlayerClass::find($get('id'))->hit_die)
                                                                    ->size(TextSize::Medium)
                                                                    ->alignment(Alignment::Center),
                                                                TextEntry::make('used')
                                                                    ->hiddenLabel()
                                                                    ->state($get('used_dice').' / '.$get('level'))
                                                                    ->prefixAction(
                                                                        Action::make('removeUsedHitDie')
                                                                            ->icon(Heroicon::Minus)
                                                                            ->action(function (Get $get, Set $set) {
                                                                                $set('used_dice', $get('used_dice') - 1);
                                                                            })
                                                                    )
                                                                    ->suffixAction(
                                                                        Action::make('addUsedHitDie')
                                                                            ->icon(Heroicon::Plus)
                                                                    )
                                                                    ->size(TextSize::Medium)
                                                                    ->alignment(Alignment::Center),
                                                            ]),
                                                        self::getDivider(),
                                                    ];

                                                    for ($i = 0; $i < $get('level'); $i++) {
                                                        $schema[] = TextInput::make('level'.($i + 1))
                                                            ->hiddenLabel()
                                                            ->prefix('Level '.($i + 1).':')
                                                            ->default($get('hp')[$i] ?? null)
                                                            ->live()
                                                            ->numeric();
                                                    }

                                                    return $schema;
                                                }))
                                            ->columnSpan(2),
                                        Tabs::make()
                                            ->tabs([
                                                Tab::make('Features')
                                                    ->schema(function (array $state, Get $get, Set $set): array {
                                                        $class = PlayerClass::find($state['id']);
                                                        $schema = [];

                                                        if ($class) {
                                                            try {
                                                                if (self::isPrimaryClass($get('../'), $class->id)) {
                                                                    $core = [
                                                                        'name' => $class->name.' Core Traits Primary Class',
                                                                        'description' => 'As a primary class the '.$class->name.' gains the following:<br>
                                                                            - <em>Saving Throw Proficiencies: '.implode(', ', array_map(fn ($item) => ucfirst($item), $class->class_info->save_prof)).'</em>',
                                                                        'mechanics' => [],
                                                                    ];

                                                                    for ($i = 0; $i < $class->class_info->amount_of_skill_prof; $i++) {
                                                                        $core['mechanics'][] = [
                                                                            'choice' => true,
                                                                            'grant' => 'skill',
                                                                            'options' => $class->class_info->skill_prof,
                                                                        ];
                                                                    }
                                                                } else {
                                                                    $core = [
                                                                        'name' => $class->name.' Core Traits Multiclass',
                                                                        'description' => 'As a multiclass the '.$class->name.' gains the following:',
                                                                        'mechanics' => [],
                                                                    ];

                                                                    for ($i = 0; $i < $class->class_info->amount_of_skill_prof; $i++) {
                                                                        $core['mechanics'][] = [
                                                                            'choice' => true,
                                                                            'grant' => 'skill',
                                                                            'options' => $class->class_info->skill_prof,
                                                                        ];
                                                                    }
                                                                }
                                                                // add weapon profs, tool profs, armor profs to core
                                                                $core['level'] = 1;
                                                                $features = ['1' => [$core]];

                                                                foreach (($class->features ?? []) as $feature) {
                                                                    $features[$feature['level']][] = $feature;
                                                                }

                                                                foreach ($features as $level) {
                                                                    foreach ($level as $feature) {
                                                                        $schema[] = ClassFeature::make($feature['name'])
                                                                            ->hiddenLabel()
                                                                            ->visible(fn (Get $get): bool => $feature['level'] <= $get('level'))
                                                                            ->referesToFeature($feature)
                                                                            ->allMechanics($state['mechanics'] ?? []);
                                                                    }
                                                                }
                                                            } catch (Exception $e) {
                                                                $set('id', null);
                                                                Notification::make('classLoadingFailed')
                                                                    ->title('Loading \''.$class->name.'\' failed')
                                                                    ->body('The loading of the class failed, this is likely because the class is not ready for use yet. Please try a different class.')
                                                                    ->danger()
                                                                    ->send();
                                                            }
                                                        }

                                                        return $schema;
                                                    }),
                                                Tab::make('Spells')
                                                    ->schema([])
                                                    ->visible(fn (Get $get): bool => PlayerClass::find($get('id'))?->spell_info?->can_cast_spells ?? false),
                                            ])
                                            ->visible(fn (array $state): bool => $state['id'] ?? false)
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
            ])
            ->columns(12);
    }

    private static function isPrimaryClass(array $classes, int|string $class): bool
    {
        foreach ($classes as $c) {
            return $c['id'] == $class;
        }

        return false;
    }

    private static function getDivider(): TextEntry
    {
        return TextEntry::make('div')
            ->hiddenLabel()
            ->state(new HtmlString('<hr class="border-gray-200 dark:border-gray-700">'));
    }
}
