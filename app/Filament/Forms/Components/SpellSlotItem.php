<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;

class SpellSlotItem extends Field
{
    protected string $view = 'filament.forms.components.spell-slot-item';

    #[ExposedLivewireMethod]
    public function stateUp($state): array
    {
        $this->state(++$state['amount']);
        $this->callAfterStateUpdated();

        return ['amount' => $state['amount']];
    }

    #[ExposedLivewireMethod]
    public function stateDown($state): array
    {
        $this->state(--$state['amount']);
        $this->callAfterStateUpdated();

        return ['amount' => $state['amount']];
    }
}
