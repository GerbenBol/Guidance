<?php

namespace App\Filament\Resources\Spells\Pages;

use App\Filament\Resources\Spells\SpellResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSpell extends EditRecord
{
    protected static string $resource = SpellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['materials'] = $data['components']['materials'] ?? '';
        $data['components'] = $data['components']['components'] ?? '';
        $range = explode(' ', $data['arearange']['range'] ?? '');
        $area = explode(' ', $data['arearange']['area'] ?? '');

        if ($range) {
            $data['range'] = $range[0] ?? '';
            $data['range_type'] = $range[1] ?? '';
        }
        if ($area) {
            $data['area'] = $area[0] ?? '';
            $data['area_type'] = strtolower($area[1] ?? '');
        }
        $data['time'] = $data['casting_time']['time'] ?? '';
        $data['casting_type'] = $data['casting_time']['type'] ?? '';
        $data['amount_of_duration'] = $data['duration']['duration'] ?? '';
        $data['concentration'] = $data['duration']['concentration'] ?? '';
        $data['duration'] = $data['duration']['type'] ?? '';

        return parent::mutateFormDataBeforeFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['components'] = [
            'components' => $data['components'],
            'materials' => in_array('m', $data['components']) ? $data['materials'] : null,
        ];
        $data['arearange'] = [
            'range' => ($data['range'] ?? '').' '.($data['range_type'] ?? ''),
            'area' => ($data['area'] ?? '').' '.ucfirst(($data['area_type'] ?? '')),
        ];
        $data['casting_time'] = [
            'time' => $data['time'],
            'type' => $data['casting_type'],
        ];
        $data['duration'] = [
            'duration' => $data['amount_of_duration'],
            'type' => $data['duration'],
            'concentration' => $data['concentration'],
        ];

        return parent::mutateFormDataBeforeSave($data);
    }
}
