<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ActionType: string implements HasLabel
{
    case Action = 'action';
    case BonusAction = 'bonus action';
    case Reaction = 'reaction';
    case FreeAction = 'free action';
    case Special = 'special';

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
