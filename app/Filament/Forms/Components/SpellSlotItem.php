<?php

namespace App\Filament\Forms\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

class SpellSlotItem extends Field
{
    protected string $view = 'filament.forms.components.spell-slot-item';

    protected ?Action $down;

    protected ?Action $up;

    public function inputDown(int $level, int $slot): static
    {
        $this->down = Action::make('lvl'.$level.'slot'.$slot.'decrease')
            ->hiddenLabel()
            ->button()
            ->size('xs')
            ->icon(Heroicon::Minus)
            ->extraAttributes(['style' => 'margin-right:5px'])
            ->action(fn (Get $get, Set $set) => $set('lvl'.$level.'slot'.$slot, $get('lvl'.$level.'slot'.$slot) - 1));

        return $this;
    }

    public function getDownAction(): ?Action
    {
        return $this->down;
    }

    public function inputUp(int $level, int $slot): static
    {
        $this->up = Action::make('lvl'.$level.'slot'.$slot.'increase')
            ->hiddenLabel()
            ->button()
            ->size('xs')
            ->icon(Heroicon::Plus)
            ->extraAttributes(['style' => 'margin-left:5px'])
            ->action(fn (Get $get, Set $set) => $set('lvl'.$level.'slot'.$slot, $get('lvl'.$level.'slot'.$slot) + 1));

        return $this;
    }

    public function getUpAction(): ?Action
    {
        return $this->up;
    }
}
