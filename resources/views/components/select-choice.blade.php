<?php

use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;

new class extends Component implements HasForms
{
    use InteractsWithForms;

    public string|int|null $value;
    public string $type;
    public string|int $id;
    public string $name;
    public array $options = [];

    public function mount(string|int|null $value = null): void {
        $this->value = $value;
        $this->form->fill(['value' => $value]);
    }

    public function form(Schema $form): Schema {
        return $form
            ->schema([
                Select::make('value')
                    ->hiddenLabel()
                    ->live()
                    ->prefix(match (preg_replace('/[0-9\-]/', '', $this->name)) {
                        default => ucfirst(preg_replace('/[0-9\-]/', '', $this->name)),
                        'adv' => 'Advantage',
                        'dadv' => 'Disadvantage',
                        'mod' => 'Modifier',
                        'res' => 'Resistance',
                        'vul' => 'Vulnerability',
                        'imm' => 'Immunity',
                        'prof' => 'Proficiency',
                        'lang' => 'Language',
                        'wm' => 'Weapon Mastery',
                    }.' Choice')
                    ->options($this->options)
                    ->searchable(),
            ]);
    }

    public function updatedValue(): void {
        $this->dispatch('choiceUpdated', name: $this->name, type: $this->type, id: $this->id, value: $this->value);
    }
};
?>

<div>
    {{ $this->form }}
</div>
