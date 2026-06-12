<?php

namespace App\Filament\Resources\Feats\Pages;

use App\Filament\Resources\Feats\FeatResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFeat extends EditRecord
{
    protected static string $resource = FeatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
