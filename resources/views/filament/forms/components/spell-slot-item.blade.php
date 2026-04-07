<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
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

        <div style="float:right">
            <x-filament::button wire:click="inputDown" size="xs" icon="heroicon-m-minus" style="margin-right:5px;"></x-filament::button>

            {{ $field->getState() ?? '0' }}

            <x-filament::button wire:click="inputUp" size="xs" icon="heroicon-m-plus" style="margin-left:5px;"></x-filament::button>
        </div>
    </div>
</x-dynamic-component>
