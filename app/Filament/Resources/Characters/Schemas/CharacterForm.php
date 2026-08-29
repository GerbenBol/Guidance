<?php

namespace App\Filament\Resources\Characters\Schemas;

use App\Filament\Forms\Components\ClassFeature;
use App\Models\Background;
use App\Models\Character;
use App\Models\Feat;
use App\Models\PlayerClass;
use App\Models\Race;
use App\Models\Skill;
use App\Models\Spell;
use App\Services\AbilityService;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
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
                                    ))
                                    ->html(),
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
                                        Section::make('Features')
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
                                                    } catch (\Exception $e) {
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
                                            })
                                            ->collapsible()
                                            ->columnSpan(fn (Get $get): int => PlayerClass::find($get('id'))?->spell_info?->can_cast_spells ? 9 : 12),
                                        Section::make('Spells')
                                            ->schema(fn (Get $get): array => [
                                                Hidden::make('chosen_cantrips')
                                                    ->default('[]'),
                                                Hidden::make('chosen_spells')
                                                    ->default('[]'),
                                                TextEntry::make('chosen')
                                                    ->hiddenLabel()
                                                    ->state(
                                                        fn (Get $get): string => 'Spells: '.count(json_decode($get('chosen_spells') ?? '[]')).'/'.PlayerClass::find($get('id'))->preparedSpellsAtLevel($get('level')).' & '.
                                                            'Cantrips: '.count(json_decode($get('chosen_cantrips') ?? '[]')).'/'.PlayerClass::find($get('id'))->cantripsAtLevel($get('level'))
                                                    )
                                                    ->beforeContent(
                                                        Action::make('openChosenSpells')
                                                            ->slideOver()
                                                            ->modalSubmitActionLabel('Done')
                                                            ->hiddenLabel()
                                                            ->tooltip('View chosen spells')
                                                            ->icon(Heroicon::Eye)
                                                            ->button()
                                                            ->schema(function (Get $get, Set $set): array {
                                                                $schema = [];
                                                                $spells = array_merge(
                                                                    json_decode($get('chosen_cantrips'), true),
                                                                    json_decode($get('chosen_spells'), true)
                                                                );

                                                                foreach (Spell::find($spells) as $spell) {
                                                                    $schema[] = Section::make($spell->name)
                                                                        ->headerActions([
                                                                            Action::make('removeSpell'.$spell->id.'ToList')
                                                                                ->hiddenLabel()
                                                                                ->icon(Heroicon::MinusCircle)
                                                                                ->size('sm')
                                                                                ->action(fn () => self::setSpellList($get, $set, $spell->id)),
                                                                        ])
                                                                        ->description(($spell->level != 0 ? 'Level '.$spell->level.' ' : '').$spell->school->name.' '.($spell->level == 0 ? 'Cantrip' : ''))
                                                                        ->schema([
                                                                            TextEntry::make('details')
                                                                                ->hiddenLabel()
                                                                                ->state(new HtmlString(
                                                                                    (isset($spell->casting_time) && $spell->casting_time != '[]' ? '<b>Casting Time:</b> '.($spell->casting_time['time'] ?? '').' '.($spell->casting_time['type'] ?? '').'<br>' : '').
                                                                                    (isset($spell->arearange) && $spell->arearange != '[]' ? '<b>Range/Area:</b> '.($spell->arearange['range'] ?? '').' / '.($spell->arearange['area'] ?? '').'<br>' : '').
                                                                                    (isset($spell->components) && $spell->components != '[]' ? '<b>Components:</b> '.(implode(', ', array_map(fn ($item) => strtoupper($item), $spell->components['components'] ?? [])) ?? '').(in_array('m', $spell->components['components'] ?? []) ? ' ('.($spell->components['materials'] ?? '').')' : '').'<br>' : '').
                                                                                    (isset($spell->duration) && $spell->duration != '[]' ? '<b>Duration:</b> '.($spell->duration['duration'] ?? '').' '.($spell->duration['type'] ?? '').($spell->duration['concentration'] ? ', Concentration' : '') : '')
                                                                                ))
                                                                                ->html(),
                                                                            self::getDivider(),
                                                                            TextEntry::make('description')
                                                                                ->hiddenLabel()
                                                                                ->state($spell->description)
                                                                                ->html(),
                                                                        ])
                                                                        ->collapsed()
                                                                        ->secondary()
                                                                        ->compact()
                                                                        ->visible(fn (): bool => in_array($spell->id, json_decode($get('chosen_cantrips') ?? '[]', true)) || in_array($spell->id, json_decode($get('chosen_spells') ?? '[]', true) ?? []));
                                                                }

                                                                return $schema;
                                                            })
                                                    )
                                                    ->afterContent(
                                                        Action::make('openAvailableSpells')
                                                            ->slideOver()
                                                            ->modalSubmitActionLabel('Done')
                                                            ->hiddenLabel()
                                                            ->tooltip('Select/Change prepared spells')
                                                            ->icon(Heroicon::ChevronRight)
                                                            ->button()
                                                            ->schema(function (Get $get, Set $set): array {
                                                                $schema = [
                                                                    Hidden::make('activeSpellFilters'),
                                                                    TextInput::make('searchSpells')
                                                                        ->hiddenLabel()
                                                                        ->placeholder('Search for a spell')
                                                                        ->live()
                                                                        ->suffixAction(
                                                                            Action::make('filterSpells')
                                                                                ->tooltip('Extra filters')
                                                                                ->icon(Heroicon::Funnel)
                                                                                ->schema([
                                                                                    Select::make('school'),
                                                                                ])
                                                                        ),
                                                                ];

                                                                foreach (self::getAvailableSpells($get('id')) as $id => $spell) {
                                                                    $schema[] = Section::make($spell->name)
                                                                        ->headerActions([
                                                                            Action::make('addSpell'.$id.'ToList')
                                                                                ->hiddenLabel()
                                                                                ->icon(fn (): Heroicon => in_array($spell->id, json_decode($get('chosen_cantrips') ?? '[]', true)) || in_array($spell->id, json_decode($get('chosen_spells') ?? '[]', true) ?? []) ? Heroicon::MinusCircle : Heroicon::PlusCircle)
                                                                                ->size('sm')
                                                                                ->action(fn () => self::setSpellList($get, $set, $id)),
                                                                        ])
                                                                        ->description(($spell->level != 0 ? 'Level '.$spell->level.' ' : '').$spell->school->name.' '.($spell->level == 0 ? 'Cantrip' : ''))
                                                                        ->schema([
                                                                            TextEntry::make('details')
                                                                                ->hiddenLabel()
                                                                                ->state(new HtmlString(
                                                                                    (isset($spell->casting_time) && $spell->casting_time != '[]' ? '<b>Casting Time:</b> '.($spell->casting_time['time'] ?? '').' '.($spell->casting_time['type'] ?? '').'<br>' : '').
                                                                                    (isset($spell->arearange) && $spell->arearange != '[]' ? '<b>Range/Area:</b> '.($spell->arearange['range'] ?? '').' / '.($spell->arearange['area'] ?? '').'<br>' : '').
                                                                                    (isset($spell->components) && $spell->components != '[]' ? '<b>Components:</b> '.(implode(', ', array_map(fn ($item) => strtoupper($item), $spell->components['components'] ?? [])) ?? '').(in_array('m', $spell->components['components'] ?? []) ? ' ('.($spell->components['materials'] ?? '').')' : '').'<br>' : '').
                                                                                    (isset($spell->duration) && $spell->duration != '[]' ? '<b>Duration:</b> '.($spell->duration['duration'] ?? '').' '.($spell->duration['type'] ?? '').($spell->duration['concentration'] ? ', Concentration' : '') : '')
                                                                                ))
                                                                                ->html(),
                                                                            self::getDivider(),
                                                                            TextEntry::make('description')
                                                                                ->hiddenLabel()
                                                                                ->state($spell->description)
                                                                                ->html(),
                                                                        ])
                                                                        ->collapsed()
                                                                        ->secondary()
                                                                        ->compact()
                                                                        ->visible(fn (Get $get): bool => ! $get('searchSpells') || str_contains(strtolower($spell->name), strtolower($get('searchSpells'))));
                                                                }

                                                                return $schema;
                                                            })
                                                            ->fillForm(self::getAvailableSpells($get('id')))
                                                    ),
                                            ])
                                            ->visible(fn (Get $get): bool => PlayerClass::find($get('id'))?->spell_info?->can_cast_spells ?? false)
                                            ->columnSpan(3)
                                            ->extraAttributes(['style' => 'position:sticky;top:80px;z-index:10']),
                                    ])
                                    ->reorderable(false)
                                    ->collapsible()
                                    ->collapseAllAction(fn (Action $action): Action => $action->visible(false))
                                    ->expandAllAction(fn (Action $action): Action => $action->visible(false))
                                    ->deleteAction(fn (Action $action): Action => $action->requiresConfirmation())
                                    ->addActionLabel('Add class')
                                    ->itemLabel(fn (array $state): string => 'Level '.$state['level'].' '.PlayerClass::find($state['id'])?->name) // + ' / ' + subclass
                                    ->columns(12),
                            ]),
                        Step::make('Race')
                            ->icon(Heroicon::OutlinedAcademicCap)
                            ->completedIcon(Heroicon::AcademicCap)
                            ->schema([
                                Select::make('race_id')
                                    ->hiddenLabel()
                                    ->prefix('Race:')
                                    ->relationship('race', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->suffixAction(
                                        Action::make('viewRaces')
                                            ->tooltip('View available races')
                                            ->icon(Heroicon::Eye)
                                            ->schema(function (Get $get, Set $set): array {
                                                $schema = [
                                                    TextInput::make('searchRaces')
                                                        ->hiddenLabel()
                                                        ->placeholder('Search for a race')
                                                        ->live(),
                                                ];

                                                foreach (Race::all() as $race) {
                                                    $$race = [];

                                                    foreach ($race->features as $feature) {
                                                        $$race[] = TextEntry::make($feature['name'])
                                                            ->hiddenLabel()
                                                            ->state('<b>'.$feature['name'].'</b><br>'.$feature['description'])
                                                            ->html();
                                                    }

                                                    $schema[] = Section::make($race->name)
                                                        ->description($race->short_desc)
                                                        ->headerActions([
                                                            Action::make('addRace'.$race->id)
                                                                ->hiddenLabel()
                                                                ->tooltip(fn (): string => $get('race_id') == $race->id ? 'Active race' : 'Choose race')
                                                                ->icon(fn (): Heroicon => $get('race_id') == $race->id ? Heroicon::CheckCircle : Heroicon::Check)
                                                                ->size('sm')
                                                                ->disabled(fn (): bool => $get('race_id') == $race->id)
                                                                ->action(fn () => $set('race_id', $race->id)),
                                                        ])
                                                        ->schema([
                                                            TextEntry::make('description')
                                                                ->hiddenLabel()
                                                                ->state($race->description),
                                                            Section::make('Features')
                                                                ->schema($$race)
                                                                ->collapsed(),
                                                        ])
                                                        ->collapsed()
                                                        ->secondary()
                                                        ->visible(fn (Get $get): bool => ! $get('searchRaces') || str_contains(strtolower($race->name), strtolower($get('searchRaces'))));
                                                }

                                                return $schema;
                                            })
                                    ),
                                Section::make('Features')
                                    ->schema(function (array $state, Get $get, Set $set): array {
                                        if ($get('race_id')) {
                                            $race = Race::find($get('race_id'));
                                            $schema = [];

                                            try {
                                                foreach ($race->features as $feature) {
                                                    $schema[] = ClassFeature::make($feature['name'])
                                                        ->hiddenLabel()
                                                        ->referesToFeature($feature)
                                                        ->allMechanics($state['race_options'] ?? []);
                                                }
                                            } catch (\Exception $e) {
                                                $set('race_id', null);
                                                Notification::make('raceLoadingFailed')
                                                    ->title('Loading \''.$race->name.'\' failed')
                                                    ->body('The loading of the race failed, this is likely because the race is not ready for use yet. Please try a different race.')
                                                    ->danger()
                                                    ->send();
                                                Log::error('Error '.$e->getCode().' while attempting to load race \''.$race->name.'\'. Message: '.$e->getMessage());
                                            }

                                            return $schema;
                                        }

                                        return [];
                                    })
                                    ->secondary(),
                            ]),
                        Step::make('Background')
                            ->icon(Heroicon::OutlinedAcademicCap)
                            ->completedIcon(Heroicon::AcademicCap)
                            ->schema([
                                Select::make('background_id')
                                    ->hiddenLabel()
                                    ->prefix('Background:')
                                    ->relationship('background', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->suffixAction(
                                        Action::make('viewBackgrounds')
                                            ->tooltip('View available backgrounds')
                                            ->icon(Heroicon::Eye)
                                            ->schema(function (Get $get, Set $set): array {
                                                $schema = [
                                                    TextInput::make('searchBackgrounds')
                                                        ->hiddenLabel()
                                                        ->placeholder('Search for a background')
                                                        ->live(),
                                                ];

                                                foreach (Background::all() as $bg) {
                                                    $$bg = 'Proficiencies: ';

                                                    foreach (collect($bg->profs)->sortBy('granted')->values()->toArray() as $prof) {
                                                        if ($prof['granted'] == 0) {
                                                            $$bg .= substr($$bg, strlen($$bg) - 2) == ': ' ? '' : ', ';
                                                            $$bg .= match ($prof['type']) {
                                                                default => $prof['options'],
                                                                'skill' => Skill::find($prof['options'])?->name
                                                            };
                                                        } elseif ($prof['granted'] == 1) {
                                                            $$bg .= '<br>Choose one from: ';

                                                            foreach ($prof['options'] as $opt) {
                                                                $$bg .= match ($prof['type']) {
                                                                    default => $opt,
                                                                    'skill' => Skill::find($opt)?->name
                                                                }.', ';
                                                            }
                                                            $$bg = substr($$bg, 0, strlen($$bg) - 2);
                                                        }
                                                    }
                                                    $$bg .= '<br><br>Feat(s): ';

                                                    foreach (collect($bg->feats)->sortBy('granted')->values()->toArray() as $feat) {
                                                        if ($feat['granted'] == 0) {
                                                            $$bg .= substr($$bg, strlen($$bg) - 2) == ': ' ? '' : ' ,';
                                                            $$bg .= Feat::find($feat['options'])?->name;
                                                        } elseif ($feat['granted'] == 1) {
                                                            $$bg .= '<br>Choose one from: ';

                                                            foreach ($feat['options'] as $opt) {
                                                                $$bg .= Feat::find($opt)?->name.', ';
                                                            }
                                                            $$bg = substr($$bg, 0, strlen($$bg) - 2);
                                                        }
                                                    }
                                                    $$bg .= '<br><br>Equipment:<br>';

                                                    foreach ($bg->equipment as $id => $option) {
                                                        $$bg .= chr(65 + $id).'. ';

                                                        foreach ($option['items'] as $item) {
                                                            $$bg .= match ($item['type']) {
                                                                'gp' => $item['amount'].' GP',
                                                                'item' => $item['amount'].' '.$item['item'],
                                                                'item-choice' => $item['amount'].' '.implode(' or ', $item['items']),
                                                                'pack' => $item['name'],
                                                                default => ''
                                                            }.', ';
                                                        }
                                                        $$bg = substr($$bg, 0, strlen($$bg) - 2).'<br>';
                                                    }

                                                    $schema[] = Section::make($bg->name)
                                                        ->description($bg->short_desc)
                                                        ->headerActions([
                                                            Action::make('addBackground'.$bg->id)
                                                                ->hiddenLabel()
                                                                ->tooltip(fn (): string => $get('background_id') == $bg->id ? 'Active background' : 'Choose background')
                                                                ->icon(fn (): Heroicon => $get('background_id') == $bg->id ? Heroicon::CheckCircle : Heroicon::Check)
                                                                ->size('sm')
                                                                ->disabled(fn (): bool => $get('background_id') == $bg->id)
                                                                ->action(fn () => $set('background_id', $bg->id)),
                                                        ])
                                                        ->schema([
                                                            TextEntry::make('description')
                                                                ->hiddenLabel()
                                                                ->state($bg->description),
                                                            Section::make('Proficiencies, Feat(s) & Equipment')
                                                                ->schema([
                                                                    TextEntry::make('pfe')
                                                                        ->hiddenLabel()
                                                                        ->state($$bg)
                                                                        ->html(),
                                                                ])
                                                                ->collapsed(),
                                                        ])
                                                        ->collapsed()
                                                        ->secondary()
                                                        ->visible(fn (Get $get): bool => ! $get('searchBackgrounds') || str_contains(strtolower($bg->name), strtolower($get('searchBackgrounds'))));
                                                }

                                                return $schema;
                                            })
                                    ),
                                Section::make('Proficiencies, Feat(s) & Equipment')
                                    ->schema(function (array $state, Get $get, Set $set): array {
                                        if ($get('background_id')) {
                                            $bg = Background::find($get('background_id'));
                                            $schema = [];

                                            try {
                                                $profs = [];
                                                $sortedProfs = collect($bg->profs)->sortBy('granted')->values()->toArray();

                                                foreach ($sortedProfs as $prof) {
                                                    if ($prof['granted'] == 0) {
                                                        $profs[] = match ($prof['type']) {
                                                            default => $prof['options'],
                                                            'skill' => Skill::find($prof['options'])?->name
                                                        };
                                                    } else {
                                                        break;
                                                    }
                                                }
                                                $schema[] = TextEntry::make('profs')
                                                    ->hiddenLabel()
                                                    ->state('Proficiencies: '.implode(', ', $profs));
                                                $sortedProfs = collect($bg->profs)->sortByDesc('granted')->values()->toArray();

                                                foreach ($sortedProfs as $id => $prof) {
                                                    if ($prof['granted'] == 1) {
                                                        $schema[] = Livewire::make('select-choice', [
                                                            'name' => $prof['type'].$id,
                                                            'options' => match ($prof['type']) {
                                                                default => [0 => 'Couldn\'t find corresponding records.'],
                                                                'skill' => Skill::find($prof['options'])->pluck('name', 'id')->toArray()
                                                            },
                                                            'type' => 'bg',
                                                            'id' => '0',
                                                            'value' => $state['background_options'][$prof['type'].$id] ?? null,
                                                        ])
                                                            ->key('bg-prof-'.$id);
                                                    } else {
                                                        break;
                                                    }
                                                }
                                                $schema[] = self::getDivider();
                                                $feats = [];
                                                $sortedFeats = collect($bg->feats)->sortBy('granted')->values()->toArray();

                                                foreach ($sortedFeats as $feat) {
                                                    if ($feat['granted'] == 0) {
                                                        $feats[] = Feat::find($feat['options'])?->name;
                                                    } else {
                                                        break;
                                                    }
                                                }
                                                $schema[] = TextEntry::make('feats')
                                                    ->hiddenLabel()
                                                    ->state('Feats: '.implode(', ', $feats));
                                                $sortedFeats = collect($bg->feats)->sortByDesc('granted')->values()->toArray();

                                                foreach ($sortedFeats as $id => $feat) {
                                                    if ($feat['granted'] == 1) {
                                                        $options = [];

                                                        foreach ($feat['options'] as $opt => $val) {
                                                            $options[] = Feat::find($val)?->name;
                                                        }
                                                        $schema[] = Livewire::make('select-choice', [
                                                            'name' => 'feat'.$id,
                                                            'options' => $options,
                                                            'type' => 'bg',
                                                            'id' => '0',
                                                            'value' => $state['background_options']['feat'.$id] ?? null,
                                                        ])
                                                            ->key('bg-feat-'.$id);
                                                    } else {
                                                        break;
                                                    }
                                                }
                                                $schema[] = self::getDivider();
                                                $equipment = [];

                                                foreach ($bg->equipment as $id => $option) {
                                                    $$id = chr(65 + $id).': (';

                                                    foreach ($option['items'] as $item) {
                                                        $$id .= match ($item['type']) {
                                                            'gp' => $item['amount'].' GP',
                                                            'item' => $item['amount'].' '.$item['item'],
                                                            'item-choice' => $item['amount'].' '.implode(' or ', $item['items']),
                                                            'pack' => $item['name'],
                                                            default => '',
                                                        }.', ';
                                                    }
                                                    $equipment[$id] = substr($$id, 0, strlen($$id) - 2).')';
                                                }
                                                $schema[] = Radio::make('equipment')
                                                    ->label('Equipment:')
                                                    ->live()
                                                    ->options($equipment);

                                                foreach ($bg->equipment as $id => $option) {
                                                    foreach ($option['items'] as $opt => $item) {
                                                        if ($item['type'] == 'item-choice') {
                                                            $items = [];

                                                            foreach ($item['items'] as $i) {
                                                                $items[$i] = $i; // Item::find($i)->name
                                                            }

                                                            $schema[] = Select::make($id.'-item-'.$opt)
                                                                ->hiddenLabel()
                                                                ->prefix('Choose an Item')
                                                                ->options($items)
                                                                ->searchable()
                                                                ->visible(fn (Get $get): bool => $get('equipment') == $id ?? false);
                                                        }
                                                    }
                                                }
                                            } catch (\Exception $e) {
                                                $set('background_id', null);
                                                Notification::make('backgroundLoadingFailed')
                                                    ->title('Loading \''.$bg->name.'\' failed')
                                                    ->body('The loading of the background failed, this is likely because the background is not ready for use yet. Please try a different background.')
                                                    ->danger()
                                                    ->send();
                                                Log::error('Error '.$e->getCode().' while attempting to load background \''.$bg->name.'\'. Message: '.$e->getMessage());
                                            }

                                            return $schema;
                                        }

                                        return [];
                                    })
                                    ->secondary(),
                            ]),
                        Step::make('Abilities')
                            ->icon(Heroicon::OutlinedAcademicCap)
                            ->completedIcon(Heroicon::AcademicCap)
                            ->schema([
                                Select::make('gen_method')
                                    ->hiddenLabel()
                                    ->prefix('Generation Method')
                                    ->live()
                                    ->native(false)
                                    ->options([
                                        'man' => 'Manual/Rolled',
                                        'buy' => 'Point Buy',
                                        'std' => 'Standard Array',
                                    ])
                                    ->default('man'), // temp
                                Section::make()
                                    ->schema([
                                        Hidden::make('used_points'),
                                        TextEntry::make('total_used_points')
                                            ->hiddenLabel()
                                            ->state(function (Get $get): string {
                                                return 'Used points: '.($get('used_points') ?? '0').'/27';
                                            })
                                            ->size('lg')
                                            ->alignCenter()
                                            ->visible(fn (Get $get): bool => $get('gen_method') == 'buy'),
                                        Repeater::make('scores')
                                            ->hiddenLabel()
                                            ->schema(function (Get $get, Set $set): array {
                                                $schema = [
                                                    Hidden::make('ability'),
                                                    TextEntry::make('ability_score')
                                                        ->hiddenLabel()
                                                        ->state(fn (Get $get) => ($get('ability') ?? '').' ('.(AbilityService::scoreToModifierString(($get('gen_method') == 'man' ? $get('score') : $get('select_score')) ?? 10)).')')
                                                        ->alignCenter()
                                                        ->size(TextSize::Large),
                                                    TextInput::make('score')
                                                        ->hiddenLabel()
                                                        ->live(onBlur: true)
                                                        ->numeric()
                                                        ->visible(fn (): bool => $get('gen_method') == 'man'),
                                                ];
                                                $scoreSelect = Select::make('select_score')
                                                    ->hiddenLabel()
                                                    ->live(onBlur: true)
                                                    ->native(false)
                                                    ->options(fn ($state): array => match ($get('gen_method')) {
                                                        'std' => [
                                                            8 => 8,
                                                            10 => 10,
                                                            12 => 12,
                                                            13 => 13,
                                                            14 => 14,
                                                            15 => 15,
                                                        ],
                                                        'buy' => self::getAvailablePoints(($get('used_points') ?? 0) - self::matchPoints($state)),
                                                        default => []
                                                    })
                                                    ->visible(fn (): bool => in_array($get('gen_method'), ['buy', 'std']))
                                                    ->afterStateUpdated(function ($state, $old) use ($get, $set) {
                                                        if ($get('gen_method') == 'buy') {
                                                            $set('used_points', ($get('used_points') ?? 0) - self::matchPoints($old) + self::matchPoints($state));
                                                        }
                                                    });

                                                if ($get('gen_method') == 'std') {
                                                    $scoreSelect->disableOptionsWhenSelectedInSiblingRepeaterItems();
                                                }
                                                $schema[] = $scoreSelect;

                                                return $schema;
                                            })
                                            ->grid([
                                                'md' => 6,
                                                'default' => 3,
                                            ])
                                            ->reorderable(false)
                                            ->deletable(false)
                                            ->addable(false)
                                            ->default('[{"ability":"Strength"},{"ability":"Dexterity"},{"ability":"Constitution"},{"ability":"Intelligence"},{"ability":"Wisdom"},{"ability":"Charisma"}]'),
                                    ])
                                    ->secondary()
                                    ->hidden(fn (Get $get): bool => ! $get('gen_method')),
                            ]),
                        Step::make('Equipment')
                            ->icon(Heroicon::OutlinedAcademicCap)
                            ->completedIcon(Heroicon::AcademicCap)
                            ->schema([]),
                    ];
                })
                    ->columnSpanFull()
                    ->startOnStep(4) // temp
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

    private static function getAvailableSpells(?int $classID): array
    {
        $spinfo = PlayerClass::find($classID)?->spell_info;
        $spells = [];

        foreach (array_merge($spinfo->spells ?? [], $spinfo->extra_spells ?? []) as $spellID) {
            $spell = Spell::find($spellID);
            $spells[$spell->id] = $spell;
        }

        return $spells;
    }

    private static function setSpellList(Get $get, Set $set, int $spellID)
    {
        $array = 'chosen_'.(Spell::find($spellID)->level == 0 ? 'cantrips' : 'spells');
        $chosen = json_decode($get($array)) ?? [];

        if (! in_array($spellID, $chosen)) {
            $chosen[] = $spellID;
        } else {
            $chosen = array_flip($chosen);
            unset($chosen[$spellID]);
            $chosen = array_flip($chosen);
        }
        $set($array, json_encode($chosen));
    }

    private static function matchPoints(?int $state): int
    {
        return match ($state) {
            default => 0,
            9 => 1,
            10 => 2,
            11 => 3,
            12 => 4,
            13 => 5,
            14 => 7,
            15 => 9
        };
    }

    private static function getAvailablePoints(int $activePoints): array
    {
        $pointsLeft = 27 - $activePoints;
        $options = [8 => '8'];
        $opt = [
            9 => 1,
            10 => 2,
            11 => 3,
            12 => 4,
            13 => 5,
            14 => 7,
            15 => 9,
        ];

        foreach ($opt as $id => $points) {
            if ($pointsLeft >= $points) {
                $options[$id] = $id.' ('.self::matchPoints($id).' Points)';
            }
        }

        return $options;
    }
}
