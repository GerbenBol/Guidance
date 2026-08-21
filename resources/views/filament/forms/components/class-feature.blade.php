<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $key = $getKey();
        $classId = explode('.', $key)[1] == 'classes' ? explode('.', $key)[2] : '';
        $feature = $field->getFeature();
        $allMechanics = $field->getMechanics();
        $pendingChoices = $totalChoices = 0;
        $choices = false;

        foreach ($feature['mechanics'] ?? [] as $id => $mechanic) {
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
            {{ $feature['name'] }}
            <span style="font-size:.8rem">
                <em style="margin-left:2px;margin-right:2px;">(Level {{ $feature['level'] }})</em>
                {!! $choices ? '<em>&middot; '.$totalChoices.' Choices, <span style="'.($pendingChoices > 0 ? 'color:lightcoral' : '').'">'.$pendingChoices.' Pending</span></em>' : '' !!}
            </span>
        </x-slot>

        {!! $feature['description'] !!}
        <br>

        @foreach ($feature['mechanics'] as $id => $mechanic)
            @if (isset($mechanic['choice']) && $mechanic['choice'])
                <br>
                @if ($classId != '')
                    @livewire('select-choice', [
                        'name' => $mechanic['grant'].$id,
                        'options' => match ($mechanic['grant']) {
                            'skill' => App\Models\Skill::find($mechanic['options'])->pluck('name', 'id')->toArray(),
                            default => [0 => 'Couldn\'t find corresponding records.'],
                        },
                        'type' => 'class',
                        'id' => $classId,
                        'value' => $allMechanics[$mechanic['grant'].$id] ?? null,
                    ], key($classId.'-'.$feature['name'].'-'.$mechanic['grant'].$id))
                @else
                    @livewire('select-choice', [
                        'name' => $mechanic['grant'].$id,
                        'options' => match ($mechanic['grant']) {
                            'skill' => App\Models\Skill::find($mechanic['options'])->pluck('name', 'id')->toArray(),
                            default => [0 => 'Couldn\'t find corresponding records.'],
                        },
                        'type' => 'race',
                        'id' => '0',
                        'value' => $allMechanics[$mechanic['grant'].$id] ?? null,
                    ], key($feature['name'].'-'.$mechanic['grant'].$id))
                @endif
            @endif
        @endforeach
    </x-filament::section>
</x-dynamic-component>
