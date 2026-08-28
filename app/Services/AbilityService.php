<?php

namespace App\Services;

class AbilityService {
    public static function scoreToModifier(int $score): int {
        return ($score - 10) / 2;
    }
}
