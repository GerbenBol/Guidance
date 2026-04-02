<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Ability: string implements HasLabel
{
    case strength = 'Strength';
    case dexterity = 'Dexterity';
    case constitution = 'Constitution';
    case intelligence = 'Intelligence';
    case wisdom = 'Wisdom';
    case charisma = 'Charisma';

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
