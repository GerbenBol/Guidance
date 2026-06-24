<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;

class ClassFeature extends Field
{
    protected string $view = 'filament.forms.components.class-feature';

    public array $feature = [];

    public array $modifiers = [];

    public function referesToFeature(array $feature): static
    {
        $this->feature = $feature;

        return $this;
    }

    public function allModifiers(array $modifiers): static
    {
        $this->modifiers = $modifiers;

        return $this;
    }

    #[ExposedLivewireMethod]
    public function getFeature(): array
    {
        return $this->feature;
    }

    #[ExposedLivewireMethod]
    public function getModifiers(): array
    {
        return $this->modifiers;
    }
}
