<?php

namespace App\Filament\Resources\Classes\Pages;

use App\Filament\Resources\Classes\ClassResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditClass extends EditRecord
{
    protected static string $resource = ClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['spell_info'])) {
            foreach ($data['spell_info'] as $key => $value) {
                $data[$key] = $value;
            }
        }

        if (isset($data['spellslots'])) {
            $levels = json_decode($data['spellslots'], true);

            foreach ($levels as $clvl => $slots) {
                foreach ($slots as $slvl => $state) {
                    $data[$clvl.'_'.$slvl] = $state;
                }
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $spell_inputs = [
            'can_cast_spells',
            'spell_ability',
            'has_own_spelllist',
            'borrows_from',
            'extra_spells',
            'spells',
            'spellslots',
        ];

        foreach ($spell_inputs as $inp) {
            if (array_key_exists($inp, $data)) {
                $data['spell_info'][$inp] = $data[$inp];
            }
        }

        return $data;
    }
}
