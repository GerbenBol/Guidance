<?php

namespace App\Filament\Resources\Feats\Pages;

use App\Filament\Resources\Feats\FeatResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFeat extends ViewRecord
{
    protected static string $resource = FeatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
