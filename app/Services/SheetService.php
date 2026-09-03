<?php

namespace App\Services;

use App\Models\Sheet;

class SheetService
{
    public static function getHitPoints(Sheet $record)
    {
        $hp = 0;

        if ($record->fixed_hp) {
            //
        } else {
            $hp += collect($record->classes)->pluck('total_hp')->sum();
        }

        return $hp + (AbilityService::scoreToModifier($record->getAbilityScore('con')) * $record->ch_lvl) + ($record->einfo->hp_mod ?? 0);
    }
}
