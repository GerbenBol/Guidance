<?php

namespace App\Filament\Forms\Components;

use App\Models\Feature;
use Filament\Forms\Components\Field;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;

class ChoiceFeature extends Field
{
    protected string $view = 'filament.forms.components.choice-feature';

    public ?Feature $feature = null;

    public array $mechanics = [];

    public function referesTo(Feature $feature): static
    {
        $this->feature = $feature;

        return $this;
    }

    public function allMechanics(array $mechanics): static
    {
        $this->mechanics = $mechanics;

        return $this;
    }
}
