<?php

namespace App\Filament\Resources\Characters\Pages;

use App\Filament\Resources\Characters\CharacterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditCharacter extends EditRecord
{
    protected static string $resource = CharacterResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Edit \''.$this->record->name.'\'';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
