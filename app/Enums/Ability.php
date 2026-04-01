<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Ability: string implements HasLabel
{
    case Strength = 'Strength';
    case Dexterity = 'Dexterity';
    case Constitution = 'Constitution';
    case Intelligence = 'Intelligence';
    case Wisdom = 'Wisdom';
    case Charisma = 'Charisma';

    public function getLabel(): string|Htmlable|null
    {
        return $this->name;
    }
}
