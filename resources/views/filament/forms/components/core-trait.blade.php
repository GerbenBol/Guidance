<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $key = $getKey();
        $classId = $field->classId;
        $trait = $field->trait;
        $allMechanics = $field->mechanics;
        $pendingChoices = $totalChoices = 0;
        $choices = false;

        foreach ($trait['mechanics'] ?? [] as $id => $mechanic) {
            if (isset($mechanic['choice']) && $mechanic['choice']) {
                $choices = true;
                $totalChoices++;

                if (!key_exists($mechanic['grant'].$id, $allMechanics) || $allMechanics[$mechanic['grant'].$id] == null) {
                    $pendingChoices++;
                }
            }
        }
    @endphp

    <style>
        .choice-pending {
            background-color: rgb(107, 56, 56);
        }
    </style>

    <x-filament::section class="{{ $pendingChoices > 0 ? 'choice-pending' : '' }}" secondary collapsible collapsed>
        <x-slot name="heading">
            {{ $trait['name'] }}
            <span style="font-size:.8rem">
                <em style="margin-left:2px;margin-right:2px;">(Level {{ $trait['level'] }})</em>
                {!! $choices ? '<em>&middot; '.$totalChoices.' Choices, <span style="'.($pendingChoices > 0 ? 'color:lightcoral' : '').'">'.$pendingChoices.' Pending</span></em>' : '' !!}
            </span>
        </x-slot>

        {!! $trait['description'] !!}
        <br>

        @foreach ($trait['mechanics'] as $id => $mechanic)
            @if (isset($mechanic['choice']) && $mechanic['choice'])
                <br>
                @livewire('select-choice', [
                    'name' => $mechanic['grant'].$id,
                    'options' => App\Services\FormService::mechanicOptions($mechanic),
                    'type' => 'class-core',
                    'id' => $classId,
                    'value' => $allMechanics[$mechanic['grant'].$id] ?? null,
                ], key($classId.'-'.$trait['name'].'-'.$mechanic['grant'].$id))
            @endif
        @endforeach
    </x-filament::section>
</x-dynamic-component>
