<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Time: string implements HasLabel
{
    case Minute = 'minute';
    case Hour = 'hour';
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';

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
