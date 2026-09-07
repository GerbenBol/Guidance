<?php

namespace App\Services;

use App\Models\PlayerClass;
use App\Models\Sheet;

class SheetService
{
    public static function getHitPoints(Sheet $record): int
    {
        $hp = 0;

        if ($record->fixed_hp) {
            foreach ($record->classes as $class) {
                $c = PlayerClass::find($class->id);
                $hitDie = (int) str_replace('d', '', $c->hit_die);
                $levels = $class->level;

                if ($hp == 0) {
                    $hp += $hitDie;
                    $levels--;
                }
                $hp += ($hitDie / 2 + 1) * $levels;
            }
        } else {
            $hp = collect($record->classes)->pluck('total_hp')->sum();
        }

        return $hp + (AbilityService::scoreToModifier($record->getAbilityScore('con')) * $record->ch_lvl) + ($record->einfo->hp_mod ?? 0);
    }

    public static function getInitiative(Sheet $record): int
    {
        return 0;
    }

    public static function getSkills(Sheet $record): object|array
    {
        return [];
    }
}
