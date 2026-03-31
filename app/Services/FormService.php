<?php

namespace App\Services;

use Filament\Schemas\Components\Icon;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class FormService
{
    public static function makeHintIcon(string $tooltip = '', string $position = 'start'): Schema
    {
        return Schema::$position(Icon::make(Heroicon::QuestionMarkCircle)->tooltip($tooltip));
    }
}
