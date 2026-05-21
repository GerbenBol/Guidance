<?php

namespace App\Filament\Resources\Backgrounds;

use App\Filament\Resources\Backgrounds\Pages\CreateBackground;
use App\Filament\Resources\Backgrounds\Pages\EditBackground;
use App\Filament\Resources\Backgrounds\Pages\ListBackgrounds;
use App\Filament\Resources\Backgrounds\Pages\ViewBackground;
use App\Filament\Resources\Backgrounds\Schemas\BackgroundForm;
use App\Filament\Resources\Backgrounds\Schemas\BackgroundInfolist;
use App\Filament\Resources\Backgrounds\Tables\BackgroundsTable;
use App\Models\Background;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BackgroundResource extends Resource
{
    protected static ?string $model = Background::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::RectangleStack;

    protected static ?string $navigationLabel = 'Backgrounds';

    protected static ?string $pluralModelLabel = 'Backgrounds';

    protected static ?string $modelLabel = 'Background';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return env('ARTICLES_GROUP', 'Articles');
    }

    public static function form(Schema $schema): Schema
    {
        return BackgroundForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BackgroundInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BackgroundsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBackgrounds::route('/'),
            'create' => CreateBackground::route('/create'),
            'view' => ViewBackground::route('/{record}'),
            'edit' => EditBackground::route('/{record}/edit'),
        ];
    }
}
