<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $key = $getKey();
    @endphp
    <style>
        .slot-item {
            background-color: rgb(73, 73, 73);
            border-radius: 10px;
            border: 1px solid gray;
            padding: 5px;
            margin: -5px;
        }
    </style>

    <div class="slot-item">
        {{ $getLabel() }}

        <input type="hidden" wire:model="{{ $getStatePath() }}" />
        
        <div style="float:right;display:flex;"
            x-data="{
                slots: { amount: {{ $field->getState() ?? '0' }} },
                async up() {
                    this.slots = await $wire.callSchemaComponentMethod(
                        @js($key),
                        'stateUp',
                        { state: this.slots }
                    )
                },
                async down() {
                    this.slots = await $wire.callSchemaComponentMethod(
                        @js($key),
                        'stateDown',
                        { state: this.slots }
                    )
                }
            }">
            <x-filament::button x-on:click="down" size="xs" icon="heroicon-m-minus" style="margin-right:5px;"></x-filament::button>

            <template x-if="slots" x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
                <p x-text="`${slots.amount}`"></p>
            </template>

            <x-filament::button x-on:click="up" size="xs" icon="heroicon-m-plus" style="margin-left:5px;"></x-filament::button>
        </div>
    </div>
</x-dynamic-component>
