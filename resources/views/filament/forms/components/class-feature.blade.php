<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $key = $getKey();
        $feature = $field->getFeature();
    @endphp

    <style>
        .choice-pending {
            background-color: rgb(107, 56, 56);
        }
    </style>

    <x-filament::section {{-- class="choice-pending" --}} secondary collapsible {{-- collapsed --}}>
        
        <x-slot name="heading">
            {{ $getLabel() }}
            <span style="font-size:.8rem">
                <em style="margin-left:2px;margin-right:2px;">(Level {{ $feature['level'] }})</em>
                {{-- <em>&middot; x Choices, <span style="color:red">x Pending</span></em> --}}
            </span>
        </x-slot>

        {{ $feature['description'] }}
    </x-filament::section>
</x-dynamic-component>
