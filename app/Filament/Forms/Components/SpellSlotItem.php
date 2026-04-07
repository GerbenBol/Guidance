<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class SpellSlotItem extends Field
{
    protected string $view = 'filament.forms.components.spell-slot-item';

    public function inputDown()
    {
        $this->state($this->getState() - 1);
    }
}
