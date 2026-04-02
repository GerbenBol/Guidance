<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Rarity: string implements HasLabel
{
    case Common = 'common';
    case Uncommon = 'uncommon';
    case Rare = 'rare';
    case VeryRare = 'very rare';
    case Legendary = 'legendary';
    case Artifact = 'artifact';

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
