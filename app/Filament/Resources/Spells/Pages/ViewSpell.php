<?php

namespace App\Filament\Resources\Spells\Pages;

use App\Filament\Resources\Spells\SpellResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSpell extends ViewRecord
{
    protected static string $resource = SpellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
