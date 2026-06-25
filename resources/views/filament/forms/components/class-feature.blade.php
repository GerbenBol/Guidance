<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $key = $getKey();
        $classId = explode('.', $key)[2];
        $feature = $field->getFeature();
        $allModifiers = $field->getModifiers();
        $pendingChoices = $totalChoices = 0;
        $choices = false;

        foreach ($feature['modifiers'] as $id => $modifier) {
            if (isset($modifier['choice']) && $modifier['choice']) {
                $choices = true;
                $totalChoices++;

                if (!key_exists($modifier['grant'].$id, $allModifiers)) {
                    $pendingChoices++; //check if it is pending or not
                }
            }
        }
    @endphp

    <style>
        .choice-pending {
            background-color: rgb(107, 56, 56);
        }
    </style>

    <x-filament::section class="{{ $pendingChoices > 0 ? 'choice-pending' : '' }}" secondary collapsible {{-- collapsed --}}>
        <x-slot name="heading">
            {{ $feature['name'] }}
            <span style="font-size:.8rem">
                <em style="margin-left:2px;margin-right:2px;">(Level {{ $feature['level'] }})</em>
                {!! $choices ? '<em>&middot; '.$totalChoices.' Choices, <span style="'.($pendingChoices > 0 ? 'color:lightcoral' : '').'">'.$pendingChoices.' Pending</span></em>' : '' !!}
            </span>
        </x-slot>

        {!! $feature['description'] !!}

        @foreach ($feature['modifiers'] as $id => $modifier)
            @if (isset($modifier['choice']) && $modifier['choice'])
                <br>
                @livewire('select-choice', [
                    'name' => $modifier['grant'].$id,
                    'options' => match ($modifier['grant']) {
                        'skill' => App\Models\Skill::find($modifier['options'])->pluck('name', 'id')->toArray(),
                        default => ['Couldn\'t find corresponding records.'],
                    },
                    'class' => $classId,
                    'value' => $allModifiers[$modifier['grant'].$id] ?? null,
                ])
            @endif
        @endforeach
    </x-filament::section>
</x-dynamic-component>
