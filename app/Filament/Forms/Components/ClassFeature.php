<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;

class ClassFeature extends Field
{
    protected string $view = 'filament.forms.components.class-feature';

    public array $feature = [];

    public array $mechanics = [];

    public function referesToFeature(array $feature): static
    {
        $this->feature = $feature;

        return $this;
    }

    public function allMechanics(array $mechanics): static
    {
        $this->mechanics = $mechanics;

        return $this;
    }

    #[ExposedLivewireMethod]
    public function getFeature(): array
    {
        return $this->feature;
    }

    #[ExposedLivewireMethod]
    public function getMechanics(): array
    {
        return $this->mechanics;
    }
}
