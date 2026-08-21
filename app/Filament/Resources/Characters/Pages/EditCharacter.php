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
        $data['race_options'] = $this->data['race_options'];
        $data['classes'] = [];
        $data['extra_info'] = [
            'use_fixed_hp' => false,
            'hp_modifier' => null,
            'overwrite_hp' => null,
        ];

        foreach ($this->data['classes'] as $class) {
            $data['classes'][] = $class;
        }

        foreach ($data['extra_info'] as $key => $val) {
            if (array_key_exists($key, $data)) {
                $data['extra_info'][$key] = $data[$key];
            }
        }

        return parent::mutateFormDataBeforeSave($data);
    }

    protected function getListeners(): array
    {
        return [
            'choiceUpdated' => 'onChoiceUpdate',
        ];
    }

    public function onChoiceUpdate(string $name, string $type, string|int $id, mixed $value): void
    {
        if ($type == 'class') {
            $this->data['classes'][$id]['mechanics'][$name] = $value;
        } elseif ($type == 'race') {
            $this->data['race_options'][$name] = $value;
        }
    }
}
