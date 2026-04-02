<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RequirementType: string implements HasLabel
{
    case Strength = 'strength';
    case Dexterity = 'dexterity';
    case Constitution = 'constitution';
    case Intelligence = 'intelligence';
    case Wisdom = 'wisdom';
    case Charisma = 'charisma';
    case Level = 'level';
    case Class = 'class';
    case Race = 'race';

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
