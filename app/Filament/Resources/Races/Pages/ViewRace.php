<?php

namespace App\Filament\Resources\Races\Pages;

use App\Filament\Resources\Races\RaceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRace extends ViewRecord
{
    protected static string $resource = RaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
