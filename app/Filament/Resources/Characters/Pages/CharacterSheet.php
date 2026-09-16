<?php

namespace App\Filament\Resources\Characters\Pages;

use App\Filament\Resources\Characters\CharacterResource;
use App\Filament\Resources\Characters\Schemas\CharacterSheetSchema;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Override;

class CharacterSheet extends EditRecord
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

    #[Override]
    protected function getFormActions(): array
    {
        return [];
    }

    public function infolist(Schema $schema): Schema
    {
        return CharacterSheetSchema::configure($schema);
    }

    public function form(Schema $schema): Schema
    {
        return CharacterSheetSchema::configure($schema);
    }
}
