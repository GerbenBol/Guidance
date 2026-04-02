<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ItemType: string implements HasLabel
{
    case Weapon = 'weapon';
    case Armor = 'armor';
    case Potion = 'potion';
    case ArcaneFocus = 'arcane focus';
    case Other = 'other';

    public function getLabel(): string|Htmlable|null
    {
        return $this->name;
    }

    public static function toArray(): array
    {
        $arr = [];

        foreach (self::cases() as $key => $value) {
            $arr[$key] = $value;
        }

        return $arr;
    }
}
