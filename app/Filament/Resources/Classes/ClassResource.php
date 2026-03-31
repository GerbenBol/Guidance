<?php

namespace App\Filament\Resources\Classes;

use App\Filament\Resources\Classes\Pages\CreateClass;
use App\Filament\Resources\Classes\Pages\EditClass;
use App\Filament\Resources\Classes\Pages\ListClasses;
use App\Filament\Resources\Classes\Pages\ViewClass;
use App\Filament\Resources\Classes\Schemas\ClassForm;
use App\Filament\Resources\Classes\Schemas\ClassInfolist;
use App\Filament\Resources\Classes\Tables\ClassesTable;
use App\Models\PlayerClass;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ClassResource extends Resource
{
    protected static ?string $model = PlayerClass::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::RectangleStack;

    protected static ?string $navigationLabel = 'Classes';

    protected static ?string $pluralModelLabel = 'Classes';

    protected static ?string $modelLabel = 'Class';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return env('ARTICLES_GROUP', 'Articles');
    }

    public static function form(Schema $schema): Schema
    {
        return ClassForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClassInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClassesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClasses::route('/'),
            'create' => CreateClass::route('/create'),
            'view' => ViewClass::route('/{record}'),
            'edit' => EditClass::route('/{record}/edit'),
        ];
    }
}
