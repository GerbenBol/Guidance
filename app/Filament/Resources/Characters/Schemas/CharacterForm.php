<?php

namespace App\Filament\Resources\Characters\Schemas;

use App\Filament\Forms\Components\ClassFeature;
use App\Models\Character;
use App\Models\PlayerClass;
use App\Models\Spell;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Flex;
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
use Illuminate\Support\Facades\Log;
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
                                            $state .= '- '.$c->name.' level '.$class['level'].' ('.$c->hit_die.'): '.$class['total_hp'].' [';

                                            foreach ($class['hp'] as $id => $hp) {
                                                $state .= ($id != 'level1' ? ' + ' : '').$hp;
                                            }
                                            $state .= ']<br>';
                                        }

                                        $state .= '- Constitution Bonus: 0 [0 * '.$record->characterLevel().']<br>';

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
                                            ->default(1),
                                        Hidden::make('dice_used')
                                            ->default('level1'),
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
                                                ->badgeColor(fn (Get $get): string => $get('level') - ($get('used_dice') ?? 0) == 0 ? 'gray' : 'primary')
                                                ->modalHeading('Edit HP')
                                                ->modalSubmitActionLabel('Save')
                                                ->fillForm(fn (Get $get) => array_merge(($get('hp') ?? ['level1' => str_replace('d', '', PlayerClass::find($get('id'))->hit_die)])))
                                                ->schema(function (Get $get, Set $set): array {
                                                    $schema = [];

                                                    for ($i = 0; $i < $get('level') - ($get('level') % 3); $i++) {
                                                        $schema[] = TextInput::make('level'.($i + 1))
                                                            ->hiddenLabel()
                                                            ->prefix('Level '.($i + 1).':')
                                                            ->live()
                                                            ->numeric()
                                                            ->afterStateUpdated(fn ($state) => self::assignDice($state, $set, $get, $i + 1));
                                                    }

                                                    if ($get('level') % 3 != 0) {
                                                        $flexSchema = [];

                                                        for ($i = $get('level') - ($get('level') % 3); $i < $get('level'); $i++) {
                                                            $flexSchema[] = TextInput::make('level'.($i + 1))
                                                                ->hiddenLabel()
                                                                ->prefix('Level '.($i + 1).':')
                                                                ->live()
                                                                ->numeric()
                                                                ->afterStateUpdated(fn ($state) => self::assignDice($state, $set, $get, $i + 1));
                                                        }
                                                        $schema[] = Flex::make($flexSchema)
                                                            ->columnSpanFull();
                                                    }

                                                    return [
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
                                                                    ->state(0)
                                                                    ->formatStateUsing(fn (): string => $get('used_dice').' / '.$get('level'))
                                                                    ->prefixAction(
                                                                        Action::make('removeUsedHitDie')
                                                                            ->tooltip('Remove rolled Hit Die')
                                                                            ->icon(Heroicon::OutlinedMinusCircle)
                                                                            ->iconSize('xl')
                                                                            ->action(fn () => $set('used_dice', (int) $get('used_dice') - 1))
                                                                    )
                                                                    ->suffixAction(
                                                                        Action::make('addUsedHitDie')
                                                                            ->tooltip('Add rolled Hit Die')
                                                                            ->icon(Heroicon::OutlinedPlusCircle)
                                                                            ->iconSize('xl')
                                                                            ->action(fn () => $set('used_dice', (int) $get('used_dice') + 1))
                                                                    )
                                                                    ->size(TextSize::Medium)
                                                                    ->alignment(Alignment::Center),
                                                            ]),
                                                        self::getDivider(),
                                                        Grid::make(count($schema) > 3 ? 3 : count($schema))
                                                            ->schema($schema),
                                                    ];
                                                })
                                                ->action(function (Set $set, Get $get, array $data) {
                                                    $levels = [];

                                                    for ($i = 0; $i < $get('level'); $i++) {
                                                        $levels['level'.($i + 1)] = $data['level'.($i + 1)];
                                                    }
                                                    $set('total_hp', collect($data)->sum());
                                                    $set('hp', $levels);
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
                                                                    $proficiencies = $class->class_info->prof;
                                                                    $core = [
                                                                        'name' => $class->name.' Core Traits Primary Class',
                                                                        'description' => 'As a primary class the '.$class->name.' gains the following:<br>
                                                                            - <em>Saving Throw Proficiencies: '.implode(', ', array_map(fn ($item) => ucfirst($item), $class->class_info->save_prof)).'</em>',
                                                                        'mechanics' => [],
                                                                    ];
                                                                } else {
                                                                    $proficiencies = $class->class_info->multiclass_prof;
                                                                    $core = [
                                                                        'name' => $class->name.' Core Traits Multiclass',
                                                                        'description' => 'As a multiclass the '.$class->name.' gains the following:',
                                                                        'mechanics' => [],
                                                                    ];
                                                                }

                                                                foreach ($proficiencies as $prof) {
                                                                    for ($i = 0; $i < $prof->amount; $i++) {
                                                                        $core['mechanics'][] = [
                                                                            'choice' => true,
                                                                            'grant' => $prof->type,
                                                                            'options' => $prof->options,
                                                                        ];
                                                                    }
                                                                }

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
                                                                Log::error('Error '.$e->getCode().' while attempting to load class \''.$class->name.'\'. Message: '.$e->getMessage());
                                                            }
                                                        }

                                                        return $schema;
                                                    }),
                                                Tab::make('Spells')
                                                    ->schema([
                                                        TextEntry::make('chosen')
                                                            ->state('Chosen Spells: 0/7')
                                                            ->afterContent(
                                                                Action::make('openChosenSpells')
                                                                    ->slideOver()
                                                                    ->schema([
                                                                        TextEntry::make('something'),
                                                                    ])
                                                            ),
                                                        TextEntry::make('available')
                                                            ->state('Available Spells')
                                                            ->afterContent(
                                                                Action::make('openChosenSpells')
                                                                    ->slideOver()
                                                                    ->schema([
                                                                        RepeatableEntry::make('available_spells')
                                                                            ->schema([
                                                                                TextEntry::make('name'),
                                                                            ])
                                                                            ->state(function (Get $get): array {
                                                                                dd($get('../id'));
                                                                                $spinfo = PlayerClass::find($get('id'))->spell_info;
                                                                                $spells = [];

                                                                                foreach (array_merge($spinfo->spells ?? [], $spinfo->extra_spells ?? []) as $spellID) {
                                                                                    $spell = Spell::find($spellID);
                                                                                    $spells[] = [
                                                                                        'name' => $spell->name,
                                                                                    ];
                                                                                }

                                                                                return $spells;
                                                                            }),
                                                                    ])
                                                            ),
                                                    ])
                                                    ->visible(fn (Get $get): bool => PlayerClass::find($get('id'))?->spell_info?->can_cast_spells ?? false),
                                            ])
                                            // ->visible(fn (array $state): bool => $state['id'] ?? false)
                                            ->activeTab(2)
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

    private static function assignDice(?string $state, Set $set, Get $get, int $id): void
    {
        $dice_used = $get('dice_used') ?? [];
        $id = 'level'.$id;

        $set('used_dice', $get('used_dice') + ($state != null ? (! in_array($id, $dice_used) ? 1 : 0) : -1));
        $set('dice_used', $state != null ? (in_array($id, $dice_used) ? $dice_used : collect($dice_used)->add($id)->toArray()) : array_values(array_filter($dice_used, fn ($item) => $item != $id)));
    }
}
