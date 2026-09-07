<?php

namespace App\Filament\Resources\Characters\Pages;

use App\Filament\Resources\Characters\CharacterResource;
use App\Filament\Resources\Characters\Schemas\CharacterSheetSchema;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class CharacterSheet extends ViewRecord
{
    protected static string $resource = CharacterResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTitle(): string|Htmlable
    {
        return '';
    }

    public function infolist(Schema $schema): Schema
    {
        return CharacterSheetSchema::configure($schema);
    }
}
