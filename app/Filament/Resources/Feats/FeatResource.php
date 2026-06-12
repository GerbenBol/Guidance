<?php

namespace App\Filament\Resources\Feats;

use App\Filament\Resources\Feats\Pages\CreateFeat;
use App\Filament\Resources\Feats\Pages\EditFeat;
use App\Filament\Resources\Feats\Pages\ListFeats;
use App\Filament\Resources\Feats\Pages\ViewFeat;
use App\Filament\Resources\Feats\Schemas\FeatForm;
use App\Filament\Resources\Feats\Schemas\FeatInfolist;
use App\Filament\Resources\Feats\Tables\FeatsTable;
use App\Models\Feat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FeatResource extends Resource
{
    protected static ?string $model = Feat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::RectangleStack;

    protected static ?string $navigationLabel = 'Feats';

    protected static ?string $pluralModelLabel = 'Feats';

    protected static ?string $modelLabel = 'Feat';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return env('ARTICLES_GROUP', 'Articles');
    }

    public static function form(Schema $schema): Schema
    {
        return FeatForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FeatInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeatsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeats::route('/'),
            'create' => CreateFeat::route('/create'),
            'view' => ViewFeat::route('/{record}'),
            'edit' => EditFeat::route('/{record}/edit'),
        ];
    }
}
