<?php

namespace App\Filament\Resources\Races\Pages;

use App\Filament\Resources\Races\RaceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRaces extends ListRecords
{
    protected static string $resource = RaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
