<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ActionType: string implements HasLabel
{
    case action = 'Action';
    case bonusAction = 'Bonus Action';
    case reaction = 'Reaction';
    case freeAction = 'Free Action';
    case special = 'Special';

    public function getLabel(): string|Htmlable|null
    {
        return $this->name;
    }

    public static function toArray(): array
    {
        $arr = [];

        foreach (self::cases() as $case) {
            $arr[$case->name] = $case->value;
        }

        return $arr;
    }
}
