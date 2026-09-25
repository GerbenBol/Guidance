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

    public static function short(string $ability): string
    {
        return match (strtolower($ability)) {
            'strength' => 'STR',
            'dexterity' => 'DEX',
            'constitution' => 'CON',
            'intelligence' => 'INT',
            'wisdom' => 'WIS',
            'charisma' => 'CHA',
            default => ''
        };
    }
}
