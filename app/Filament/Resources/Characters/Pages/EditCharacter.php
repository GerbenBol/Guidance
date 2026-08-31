<?php

namespace App\Filament\Resources\Characters\Pages;

use App\Filament\Resources\Characters\CharacterResource;
use App\Models\Background;
use App\Models\Character;
use App\Models\Sheet;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
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
            Action::make('generateSheet')
                ->label('Prepare Sheet')
                ->icon(Heroicon::Cog)
                ->action(function (Character $record) {
                    if (! $record->sheet) {
                        $sheet = Sheet::create([
                            'character_id' => $this->record->id,
                        ]);
                    } else {
                        $sheet = $record->sheet;
                    }
                    $sheet->generate();
                })
                ->visible(fn (Character $record): bool => ! $record->sheet?->isUpToDate() ?? true),
            Action::make('openSheet')
                ->label('Open Sheet')
                ->icon(Heroicon::PaperAirplane)
                ->url('sheet')
                ->visible(fn (Character $record): bool => $record->sheet?->isUpToDate() ?? false),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['race_options'] = $this->data['race_options'];
        $data['background_options'] = $this->data['background_options']; // ?? [] + ['equip' => $data['equipment'] ?? []];
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

        // foreach (Background::find($data['background_id'])?->equipment[$data['bg_equipment']]['items'] ?? [] as $id => $item) {
        //     if ($item['type'] == 'item-choice') {
        //         $data['background_options']['equip-choice']['bg-'.$data['bg_equipment'].'-item-'.$id] = $data['bg-'.$data['bg_equipment'].'-item-'.$id];
        //     }
        // }

        $data['extra_info']['gen_method'] = $data['gen_method'];
        $data['extra_info']['used_points'] = $data['used_points'];
        $data['extra_info']['scores'] = $data['scores'];

        if ($data['gen_method'] != 'man') {
            foreach ($data['extra_info']['scores'] as $id => $score) {
                $data['extra_info']['scores'][$id]['score'] = $score['select_score'];
                unset($data['extra_info']['scores'][$id]['select_score']);
            }
        }

        return parent::mutateFormDataBeforeSave($data);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // $data['bg_equipment'] = $data['background_options']['equip'] ?? [];

        // foreach ($data['background_options']['equip-choice'] ?? [] as $opt => $val) {
        //     $data[$opt] = $val;
        // }
        $data['gen_method'] = $data['extra_info']['gen_method'] ?? null;
        $data['used_points'] = $data['extra_info']['used_points'] ?? null;
        $data['scores'] = $data['extra_info']['scores'] ?? [];

        if ($data['gen_method'] != 'man') {
            foreach ($data['scores'] as $id => $score) {
                $data['scores'][$id]['select_score'] = $score['score'];
            }
        }

        return parent::mutateFormDataBeforeFill($data);
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
        } elseif ($type == 'bg') {
            $this->data['background_options'][$name] = $value;
        }
    }
}
