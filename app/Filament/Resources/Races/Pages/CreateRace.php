<?php

namespace App\Filament\Resources\Races\Pages;

use App\Filament\Resources\Races\RaceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRace extends CreateRecord
{
    protected static string $resource = RaceResource::class;

    protected function getFormActions(): array
    {
        return [
            parent::getSubmitFormAction(),
            parent::getCancelFormAction(),
        ];
    }
}
