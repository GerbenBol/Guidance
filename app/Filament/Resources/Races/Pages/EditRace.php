<?php

namespace App\Filament\Resources\Races\Pages;

use App\Filament\Resources\Races\RaceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRace extends EditRecord
{
    protected static string $resource = RaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
