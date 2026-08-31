<?php

namespace App\Filament\Resources\Classes\Pages;

use App\Filament\Resources\Classes\ClassResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditClass extends EditRecord
{
    protected static string $resource = ClassResource::class;

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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['class_info'])) {
            foreach ($data['class_info'] as $key => $value) {
                $data[$key] = in_array($key, ['prof', 'multiclass_prof']) ? json_decode(json_encode($value), true) : $value;
            }
        }

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

        $data['equipment'] = json_decode(json_encode($data['equipment']), true);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $class_inputs = [
            'primary_ability',
            'secondary_ability',
            'save_prof',
            'prof',
            'multiclass_prof',
            'asi_lvls',
            'subclass_name',
            'subclass_start_lvl',
            'equipment',
        ];

        $spell_inputs = [
            // 'cantrips',
            'can_cast_spells',
            'spell_ability',
            'has_own_spelllist',
            'knows_full_spelllist',
            'regains_spells_on',
            'known_prepared_amounts',
            'borrows_from',
            'extra_spells',
            'spells',
            'extra_spells',
            'spellslots',
        ];

        foreach ($class_inputs as $inp) {
            if (array_key_exists($inp, $data)) {
                $data['class_info'][$inp] = $data[$inp];
            }
        }

        foreach ($spell_inputs as $inp) {
            if (array_key_exists($inp, $data)) {
                $data['spell_info'][$inp] = $data[$inp];
            }
        }

        return $data;
    }
}
