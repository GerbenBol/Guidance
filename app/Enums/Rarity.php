<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Rarity: string implements HasLabel
{
    case common = 'Common';
    case uncommon = 'Uncommon';
    case rare = 'Rare';
    case veryRare = 'Very Rare';
    case legendary = 'Legendary';
    case artifact = 'Artifact';

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
