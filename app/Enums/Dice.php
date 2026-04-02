<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Dice: string implements HasLabel
{
    case d4 = 'd4';
    case d6 = 'd6';
    case d8 = 'd8';
    case d10 = 'd10';
    case d12 = 'd12';
    case d20 = 'd20';
    case d100 = 'd100';

    public function getLabel(): string|Htmlable|null
    {
        return $this->name;
    }
}
