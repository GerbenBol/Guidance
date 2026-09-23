<?php

namespace App\Filament\Resources\Characters\Schemas;

use App\Enums\Ability;
use App\Models\Character;
use App\Models\Sheet;
use App\Services\AbilityService;
use App\Services\SheetService;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

use function Laravel\Prompts\text;

class CharacterSheetSchema
{
    public static function configure(Schema $schema, SheetService $serve): Schema
    {
        return $schema
            ->components(function (Character $record) use ($serve) {
                // dd($record->sheet);
                // dd($serve->get('abilities'));

                // if (! $record->sheet) {
                //     Sheet::create([
                //         'character_id' => $record->id,
                //     ])->generate();
                // } elseif (! $record->sheet->isUpToDate()) {
                //     $record->sheet->generate();
                // }
                // dd($record->sheet->abilities);

                return [
                    Section::make()
                        ->schema([
                            Hidden::make('inspiration')
                                ->default(false),
                            TextEntry::make('name')
                                ->hiddenLabel()
                                ->formatStateUsing(fn (string $state): string => '<b>'.$state.'</b><br>'.
                                    '<em>'.$serve->get('race')->name.' - '.
                                    implode(' / ', $record->sheet?->classes()->pluck('name')->toArray() ?? []).'</em>'
                                )
                                ->prefixAction(
                                    Action::make('manage')
                                        ->hiddenLabel()
                                        ->tooltip('Manage Character')
                                        ->icon(Heroicon::Cog)
                                        ->url('edit'),
                                )
                                ->afterContent(
                                    Actions::make([
                                        Action::make('inspo')
                                            ->hiddenLabel()
                                            ->tooltip(fn (Get $get): string => 'Has inspiration: '.($get('inspiration') ? 'Yes' : 'No'))
                                            ->icon(fn (Get $get): Heroicon => $get('inspiration') ? Heroicon::Bolt : Heroicon::BoltSlash)
                                            ->iconButton()
                                            ->action(fn (Get $get, Set $set) => $set('inspiration', !$get('inspiration')))
                                    ])
                                    ->alignCenter()
                                    ->aboveContent('Inspiration')
                                )
                                ->html()
                        ])
                        ->columnSpan(4),
                    self::emptySpace()
                        ->columnSpan(3),
                    Section::make()
                        ->schema([
                            Flex::make([
                                TextInput::make('hp_to_heal')
                                    ->hiddenLabel()
                                    // ->numeric()
                                    ->prefixAction(
                                        Action::make('heal')
                                            ->hiddenLabel()
                                            ->icon(Heroicon::Plus)
                                            // ->action(fn ($state) => $serve->get('hp') = $serve->get('hp') + $state)
                                    )
                                    ->suffixAction(
                                        Action::make('damage')
                                            ->hiddenLabel()
                                            ->icon(Heroicon::Minus)
                                    ),
                                TextEntry::make('current_hp')
                                    ->hiddenLabel()
                                    ->state(fn () => 'HP: '.$serve->get('hp').' / '.$serve->get('hp'))
                                    ->size(TextSize::Large)
                                    ->alignCenter()
                                    ->grow(false),
                                TextEntry::make('temp_hp')
                                    ->hiddenLabel()
                                    ->state(fn () => 'Temp: '.($serve->get('temp_hp') ?? '--'))
                                    ->size(TextSize::Large)
                                    ->alignCenter()
                                    ->grow(false)
                            ])
                        ])
                        ->columnSpan(4),
                    Actions::make([
                        Action::make('short_rest')
                            ->hiddenLabel()
                            ->tooltip('Short Rest')
                            ->icon(Heroicon::BellSnooze),
                        Action::make('long_rest')
                            ->hiddenLabel()
                            ->tooltip('Long Rest')
                            ->icon(Heroicon::Moon)
                    ]),
                    Grid::make(1)
                        ->schema([
                            Section::make()
                                ->schema([
                                    RepeatableEntry::make('abilities')
                                        ->hiddenLabel()
                                        ->schema([
                                            TextEntry::make('ability')
                                                ->hiddenLabel()
                                                ->alignCenter()
                                                ->size(TextSize::Small)
                                                ->formatStateUsing(fn ($state): string => AbilityService::short($state))
                                                ->extraAttributes(['style' => 'margin-top:-5px;']),
                                            TextEntry::make('mod')
                                                ->hiddenLabel()
                                                ->alignCenter()
                                                ->default('+0')
                                                ->size(TextSize::Medium)
                                                ->extraAttributes(['style' => 'margin-top:-15px;border:1px solid white;border-radius:5px']),
                                            TextEntry::make('score')
                                                ->hiddenLabel()
                                                ->alignCenter()
                                                ->size(TextSize::Small)
                                                ->extraAttributes(['style' => 'margin-top:-15px;border:1px solid white;border-radius:5px'])
                                        ])
                                        ->getStateUsing(fn () => $serve->get('abilities'))
                                        ->grid(6),
                                ]),
                            // Section::make()
                            //     ->schema([
                            //         //
                            //     ]),
                            
                        ])
                        ->columnSpan(6),
                    Grid::make(1)
                        ->schema([
                            Section::make()
                                ->schema([
                                    Flex::make([
                                        TextEntry::make('pb')
                                            ->hiddenLabel()
                                            ->getStateUsing(fn () => 'Proficiency<br>Bonus<br><span style="font-size:1.5rem">+3</span>')
                                            ->extraAttributes(['style' => 'text-align:center'])
                                            ->html(),
                                        TextEntry::make('speed')
                                            ->hiddenLabel()
                                            ->getStateUsing(fn () => 'Walking<br>Speed<br><span style="font-size:1.5rem">30</span>')
                                            ->extraAttributes(['style' => 'text-align:center'])
                                            ->html(),
                                        TextEntry::make('init')
                                            ->hiddenLabel()
                                            ->getStateUsing(fn () => 'Initiative<br><span style="font-size:1.5rem">+2</span>')
                                            ->extraAttributes(['style' => 'text-align:center'])
                                            ->html(),
                                        TextEntry::make('ac')
                                            ->hiddenLabel()
                                            ->getStateUsing(fn () => 'Armor<br>Class<br><span style="font-size:1.5rem">13</span>')
                                            ->extraAttributes(['style' => 'text-align:center'])
                                            ->html()
                                    ]),
                                ]),
                            
                        ])
                        ->columnSpan(6)
                ];
            })
            ->columns(12);
    }

    private static function emptySpace(): TextEntry {
        return TextEntry::make('empty')
            ->hiddenLabel();
    }
}
