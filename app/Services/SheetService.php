<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Feature;
use App\Models\PlayerClass;
use App\Models\Sheet;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

class SheetService
{
    public static function getHitPoints(Sheet $record): int
    {
        $hp = 0;

        if ($record->fixed_hp) {
            foreach ($record->classes as $class) {
                $c = PlayerClass::find($class->id);
                $hitDie = (int) str_replace('d', '', $c->hit_die);
                $levels = $class->level;

                if ($hp == 0) {
                    $hp += $hitDie;
                    $levels--;
                }
                $hp += ($hitDie / 2 + 1) * $levels;
            }
        } else {
            $hp = collect($record->classes)->pluck('total_hp')->sum();
        }

        return $hp + (AbilityService::scoreToModifier($record->getAbilityScore('con')) * $record->ch_lvl) + ($record->einfo->hp_mod ?? 0);
    }

    public static function getInitiative(Sheet $record): int
    {
        return 0;
    }

    public static function getSkills(Sheet $record): object|array
    {
        return [];
    }

    public function getTabSchema(string $tab): array
    {
        return match ($tab) {
            default => [],
            'prof' => [
                TextInput::make('name'),
            ],
            'actions' => [
                Section::make('jnfr'),
            ],
            'features' => $this->getFeaturesSchema(),
        };
    }

    private Sheet $sheet;

    public function __construct(Character $chara)
    {
        $sheet = null;

        if (! $chara->sheet) {
            $sheet = Sheet::create([
                'character_id' => $chara->id,
            ]);
        } elseif (! $chara->sheet->isUpToDate()) {
            $sheet = $chara->sheet;
        }
        $sheet?->generate();
        $this->sheet = $sheet ?? $chara->sheet;
    }

    public function get(string $get)
    {
        return $this->sheet->$get;
    }

    public function getCurrent(string $get)
    {
        //
    }

    private function getFeaturesSchema(): array
    {
        $schema = [
            Section::make('Class Features')
                ->schema(function () {
                    $schema = [];

                    foreach ($this->sheet->classes as $class) {
                        $c = PlayerClass::find($class->id);
                        $schema[] = Section::make($c->name.' Features')
                            ->schema(function () use ($c, $class) {
                                $schema = [];

                                foreach ($c->features ?? [] as $feature) {
                                    $options = self::getFeatureOptions(collect($class->mechanics)->toArray(), $feature->id);
                                    $schema[] = $this->convertFeatureToSchema($feature, $options);
                                }

                                return $schema;
                            })
                            ->collapsible()
                            ->compact();
                    }

                    return $schema;
                })
                ->collapsible()
                ->compact()
                ->secondary(),
            Section::make('Race Features')
                ->schema(function () {
                    $schema = [];

                    foreach ($this->sheet->race->features as $feature) {
                        $options = self::getFeatureOptions(collect($this->sheet->race_options)->toArray(), $feature->id);
                        $schema[] = $this->convertFeatureToSchema($feature, $options);
                    }

                    return $schema;
                })
                ->collapsible()
                ->compact()
                ->secondary(),
        ];

        return $schema;
    }

    private function getFeatureOptions(array $options, int $id): array
    {
        $ret = [];

        foreach ($options as $key => $option) {
            if (str_contains($key, '-')) {
                $opt = explode('-', $key);

                if ($opt[0] == $id) {
                    $ret[preg_replace('/[^0-9]/', '', $opt[1])] = $option;
                }
            }
        }

        return $ret;
    }

    private function convertFeatureToSchema(Feature $feature, array $options): TextEntry
    {
        return TextEntry::make($feature->name)
            ->hiddenLabel()
            ->state('<b>'.$feature->name.'</b><br>'.$feature->snippet)
            ->html()
            ->action(
                Action::make($feature->name)
                    ->slideOver()
                    ->modalDescription('Level '.$feature->level.' feature')
                    ->schema([
                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->state($feature->description)
                            ->html(),
                        Section::make('Mechanics')
                            ->schema([
                                TextEntry::make('mechs')
                                    ->hiddenLabel()
                                    ->state(function () use ($feature, $options) {
                                        $state = '';

                                        foreach ($feature->mechanics as $id => $mechanic) {
                                            if ($mechanic['choice']) {
                                                $state .= ModifierService::mechanicToText($mechanic, $options[$id]);
                                            } else {
                                                $state .= ModifierService::mechanicToText($mechanic);
                                            }
                                            $state .= '<br>';
                                        }

                                        return $state;
                                    })
                                    ->html(),
                            ])
                            // ->schema(function () use ($feature): array {
                            //     $schema = [];

                            //     foreach ($feature->mechanics as $id => $mechanic) {
                            //         $schema[] = Section::make()
                            //             ->schema([
                            //                 TextEntry::make('mech'.$id)
                            //                     ->hiddenLabel()
                            //                     ->state(ModifierService::mechanicToText($mechanic))
                            //             ])
                            //             ->compact(); // could also have a textinput instead of textentry if has choices
                            //     }
                            //     return $schema;
                            // })
                            ->compact()
                            ->secondary()
                            ->collapsed()
                            ->visible(fn (): bool => ! empty($feature->mechanics)),
                    ])
            );
    }
}
