<?php

namespace App\Filament\Resources\Backgrounds\Pages;

use App\Filament\Resources\Backgrounds\BackgroundResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBackground extends ViewRecord
{
    protected static string $resource = BackgroundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
