<?php

namespace App\Services;

class AbilityService
{
    public static function scoreToModifier(int $score): int
    {
        return ($score - 10) / 2;
    }

    public static function scoreToModifierString(int $score): string|int
    {
        $mod = self::scoreToModifier($score);

        return $mod >= 0 ? '+'.$mod : $mod;
    }
}
