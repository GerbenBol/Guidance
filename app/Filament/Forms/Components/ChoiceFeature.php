<?php

namespace App\Filament\Forms\Components;

use App\Models\Feature;
use App\Models\Skill;
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

    #[ExposedLivewireMethod]
    public function getFeature(): Feature
    {
        return $this->feature;
    }

    #[ExposedLivewireMethod]
    public function getMechanics(): array
    {
        return $this->mechanics;
    }

    #[ExposedLivewireMethod]
    public function mechanicOptions(array $mechanic): array
    {
        $options = [];

        foreach ($mechanic['options'] ?? [] as $opt) {
            $explopt = explode('-', $opt);
            $options[] = match ($explopt[0]) {
                default => $opt,
                'skill' => Skill::find($explopt[1])
            };
        }

        return count($options) > 0 ? $options : [0 => 'No options assigned.'];
    }
}
