<?php

namespace App\Filament\Resources\Spells\Schemas;

use App\Enums\ActionType;
use App\Enums\Dice;
use App\Enums\Time;
use App\Models\DamageType;
use App\Models\School;
use App\Services\FormService;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class SpellForm
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
                                Flex::make([
                                    TextInput::make('name')
                                        ->required(),
                                    FormService::getSystemVersionInput(),
                                    Select::make('level')
                                        ->options([
                                            'Cantrip', 1, 2, 3,
                                            4, 5, 6, 7, 8, 9,
                                        ])
                                        ->native(false),
                                    Select::make('school_id')
                                        ->label('Magic School')
                                        ->options(School::all()->pluck('name', 'id'))
                                        ->searchable()
                                        ->native(false),
                                ])->from('sm'),
                                RichEditor::make('short_desc')
                                    ->label('Short Description'),
                                RichEditor::make('description'),
                            ]),
                        Tab::make('Details')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Fieldset::make('Components')
                                            ->schema([
                                                CheckboxList::make('components')
                                                    ->options([
                                                        'v' => 'Verbal',
                                                        's' => 'Somatic',
                                                        'm' => 'Material',
                                                    ])
                                                    ->live(),
                                                Textarea::make('materials')
                                                    ->rows(4)
                                                    ->autosize()
                                                    ->disabled(fn (Get $get): bool => ! in_array('m', $get('components')))
                                                    ->columnSpan(3),
                                            ])
                                            ->columns(4),
                                        Fieldset::make('Range/ Area')
                                            ->schema([
                                                FusedGroup::make([
                                                    TextInput::make('range')
                                                        ->numeric()
                                                        ->visible(fn (Get $get): bool => in_array($get('range_type'), ['feet', 'mile', 'inch'])),
                                                    Select::make('range_type')
                                                        ->options([
                                                            'self' => 'Self',
                                                            'touch' => 'Touch',
                                                            'inch' => 'Inch',
                                                            'feet' => 'Feet',
                                                            'mile' => 'Mile',
                                                        ])
                                                        ->native(false)
                                                        ->columnSpan(fn (Get $get): int => in_array($get('range_type'), ['feet', 'mile', 'inch']) ? 3 : 4)
                                                        ->live(),
                                                ])
                                                    ->columns(4),
                                                FusedGroup::make([
                                                    TextInput::make('area')
                                                        ->suffix('ft.'),
                                                    Select::make('area_type')
                                                        ->options([
                                                            'emanation' => 'Emanation',
                                                            'line' => 'Line',
                                                            'cube' => 'Cube',
                                                            'cylinder' => 'Cylinder',
                                                            'sphere' => 'Sphere',
                                                        ])
                                                        ->native(false)
                                                        ->columnSpan(3),
                                                ])
                                                    ->columns(4),
                                            ])
                                            ->columns(1),
                                        Fieldset::make('Casting Time')
                                            ->schema([
                                                FusedGroup::make([
                                                    TextInput::make('time')
                                                        ->numeric(),
                                                    Select::make('casting_type')
                                                        ->options(ActionType::toArray() + Time::toArray())
                                                        ->native(false)
                                                        ->live()
                                                        ->columnSpan(3),
                                                ])
                                                    ->columns(4),
                                                Textarea::make('special')
                                                    ->label('Special Description')
                                                    ->autosize()
                                                    ->visible(fn (Get $get): bool => $get('casting_type') == 'special'),
                                            ])
                                            ->columns(1),
                                        Fieldset::make('Duration')
                                            ->schema([
                                                FusedGroup::make([
                                                    TextInput::make('amount_of_duration')
                                                        ->label(fn (Get $get): string => 'Amount of '.ucfirst($get('duration')))
                                                        ->numeric()
                                                        ->visible(fn (Get $get): bool => $get('duration') && $get('duration') != 'instantanious'),
                                                    Select::make('duration')
                                                        ->options(['instantanious' => 'Instantanious'] + Time::toArray())
                                                        ->native(false)
                                                        ->live()
                                                        ->columnSpan(fn (Get $get): int => $get('duration') && $get('duration') != 'instantanious' ? 3 : 4),
                                                ])
                                                    ->columns(4)
                                                    ->columnSpan(2),
                                                Checkbox::make('concentration'),
                                            ])
                                            ->columns(3),
                                    ]),
                            ]),
                        Tab::make('Effects')
                            ->schema([
                                Section::make('Effect')
                                    ->schema([
                                        Repeater::make('effect')
                                            ->hiddenLabel()
                                            ->schema([
                                                Select::make('type')
                                                    ->label('Type')
                                                    ->options(['hp' => 'Health', 'temp' => 'Temporary HP'] + DamageType::all()->pluck('name', 'id')->toArray())
                                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                                    ->searchable()
                                                    ->native(false),
                                                TextInput::make('amount')
                                                    ->label('Amount of Dice')
                                                    ->default(0)
                                                    ->numeric(),
                                                Select::make('dice')
                                                    ->label('Dice Type')
                                                    ->options(Dice::toArray() + ['straight' => 'No Roll'])
                                                    ->searchable()
                                                    ->native(false),
                                            ])
                                            ->columns(3)
                                            ->addActionLabel('Add effect'),
                                    ])
                                    ->collapsible(),
                                Section::make('Scaling')
                                    ->schema([
                                        RichEditor::make('scale_desc')
                                            ->label('Description'),
                                        Repeater::make('scaling')
                                            ->hiddenLabel()
                                            ->schema([
                                                Select::make('scale_moment')
                                                    ->label('Moment when scale happens')
                                                    ->options([
                                                        'Per Character Level',
                                                        'Per Spell Level',
                                                        'Certain Character Levels',
                                                        'Certain Spell Levels',
                                                        'Once',
                                                    ])
                                                    ->native(false),
                                                Select::make('type')
                                                    ->label('Type')
                                                    ->options(['hp' => 'Health', 'temp' => 'Temporary HP'] + DamageType::all()->pluck('name', 'id')->toArray())
                                                    ->searchable()
                                                    ->native(false),
                                                TextInput::make('amount')
                                                    ->label('Amount of Dice')
                                                    ->default(0)
                                                    ->numeric(),
                                                Select::make('dice')
                                                    ->label('Dice Type')
                                                    ->options(Dice::toArray() + ['straight' => 'No Roll'])
                                                    ->searchable()
                                                    ->native(false),
                                            ])
                                            ->columns(4)
                                            ->addActionLabel('Add scaling'),
                                    ])
                                    ->collapsible(),
                            ]),
                    ]),
            ])
            ->columns(1);
    }
}
