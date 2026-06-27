<?php

namespace App\Services;

class ModifierService
{
    public static function validateModifier(?string $modifier): int
    {
        if (! in_array(null, self::splitModifier($modifier))) {
            return 2;
        }

        return 1;
    }

    public static function splitModifier(?string $modifier): array
    {
        $modifier = str_replace(' ', '', $modifier);
        $modifiers = [];

        foreach (preg_split('/([+\-])/', $modifier, flags: PREG_SPLIT_DELIM_CAPTURE) as $mod) {
            if (in_array($mod, ['+', '-', '*', '/'])) {
                $modifiers[] = [
                    'type' => 'addSub',
                    'do' => $mod,
                ];
            } elseif (in_array($mod, ['PB', 'LVL', 'STR', 'DEX', 'CON', 'INT', 'WIS', 'CHA'])) {
                $modifiers[] = [
                    'type' => 'character',
                    'do' => $mod,
                ];
            } elseif (str_contains($mod, 'd')) {
                $split = explode('d', $mod);
                if (! is_numeric($split[0]) || ! is_numeric($split[1])) {
                    $modifiers[] = null;
                } else {
                    $modifiers[] = [
                        'type' => 'dice',
                        'amount' => $split[0],
                        'dice' => 'd'.$split[1],
                    ];
                }
            } elseif (is_numeric($mod)) {
                $modifiers[] = [
                    'type' => 'straight',
                    'modifier' => $mod,
                ];
            } else {
                $modifiers[] = null;
            }
        }

        return $modifiers;
    }
}
