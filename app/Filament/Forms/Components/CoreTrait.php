<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;

class CoreTrait extends Field {
    protected string $view = 'filament.forms.components.core-trait';

    public array $trait = [];

    public array $mechanics = [];

    public int $classId = 0;

    public function refereToTrait(array $trait): static {
        $this->trait = $trait;
        return $this;
    }

    public function allMechanics(array $mechanics): static {
        $this->mechanics = $mechanics;
        return $this;
    }

    public function classId(int $id): static {
        $this->classId = $id;
        return $this;
    }
}
