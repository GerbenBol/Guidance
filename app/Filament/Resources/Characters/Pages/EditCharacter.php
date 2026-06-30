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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['classes'] = [];

        foreach ($this->data['classes'] as $class) {
            $data['classes'][] = [
                'id' => $class['id'],
                'level' => $class['level'],
                'hp' => $class['hp'],
                'used_dice' => $class['used_dice'],
                'mechanics' => $class['mechanics'],
            ];
        }

        return parent::mutateFormDataBeforeSave($data);
    }

    protected function getListeners(): array
    {
        return [
            'choiceUpdated' => 'onChoiceUpdate',
        ];
    }

    public function onChoiceUpdate(string $name, string $class, mixed $value): void
    {
        $this->data['classes'][$class]['mechanics'][$name] = $value;
    }
}
