<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ItemType: string implements HasLabel
{
    case weapon = 'Weapon';
    case armor = 'Armor';
    case potion = 'Potion';
    case arcaneFocus = 'Arcane Focus';
    case other = 'Other';

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
