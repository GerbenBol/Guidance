<?php

use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;

new class extends Component implements HasForms
{
    use InteractsWithForms;

    public ?int $value;
    public string $class;
    public string $name;
    public array $options = [];

    public function mount(?int $value = null): void {
        $this->value = $value;
        $this->form->fill(['value' => $value]);
    }

    public function form(Schema $form): Schema {
        return $form
            ->schema([
                Select::make('value')
                    ->hiddenLabel()
                    ->live()
                    ->prefix('Choose a '.preg_replace('/\d/', '', ucfirst($this->name)))
                    ->options($this->options)
                    ->searchable(),
            ]);
    }

    public function updatedValue(): void {
        $this->dispatch('choiceUpdated', name: $this->name, class: $this->class, value: $this->value);
    }
};
?>

<div>
    {{ $this->form }}
</div>