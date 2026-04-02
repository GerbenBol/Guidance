<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Time: string implements HasLabel
{
    case minute = 'Minute(s)';
    case hour = 'Hour(s)';
    case day = 'Day(s)';
    case week = 'Week(s)';
    case month = 'Month(s)';
    case year = 'Year(s)';

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
