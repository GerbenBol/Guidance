<?php

namespace App\Filament\Resources\Characters\Pages;

use App\Filament\Resources\Characters\CharacterResource;
use App\Filament\Resources\Characters\Schemas\CharacterSheetSchema;
use App\Services\SheetService;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

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

    protected function getFormActions(): array
    {
        return [];
    }

    public function infolist(Schema $schema): Schema
    {
        $serve = new SheetService($this->record);

        return CharacterSheetSchema::configure($schema, $serve);
    }

    public function form(Schema $schema): Schema
    {
        $serve = new SheetService($this->record);

        return CharacterSheetSchema::configure($schema, $serve);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['layout'] = $data['layout'] ?? '{"tabs_amount":2,"containers":[{"tabs":["prof"]},{"tabs":["features","actions"]}]}';

        return parent::mutateFormDataBeforeFill($data);
    }
}
