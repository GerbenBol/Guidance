<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $key = $getKey();
        $feature = $field->getFeature();
        $allMechanics = $field->getMechanics();
        $parent = $feature->parent;
        $pendingChoices = $totalChoices = 0;
        $choices = false;

        foreach ($feature->mechanics ?? [] as $id => $mechanic) {
            if (isset($mechanic['choice']) && $mechanic['choice']) {
                $choices = true;
                $totalChoices++;

                if (!key_exists($feature->id.$mechanic['grant'].$id, $allMechanics) || $allMechanics[$feature->id.$mechanic['grant'].$id] == null) {
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
            {{ $feature->name }}
            <span style="font-size:.8rem">
                <em style="margin-left:2px;margin-right:2px;">(Level {{ $feature->level }})</em>
                {!! $choices ? '<em>&middot; '.$totalChoices.' Choices, <span style="'.($pendingChoices > 0 ? 'color:lightcoral' : '').'">'.$pendingChoices.' Pending</span></em>' : '' !!}
            </span>
        </x-slot>

        {!! $feature->description !!}
        <br>

        @foreach ($feature->mechanics as $id => $mechanic)
            @if (isset($mechanic['choice']) && $mechanic['choice'])
                <br>
                @livewire('select-choice', [
                    'name' => $feature->id.$mechanic['grant'].$id,
                    // 'options' => match ($mechanic['grant']) {
                    //     default => [0 => 'Couldn\'t find options.'],
                    //     'skill' => App\Models\Skill::find($mechanic['options'])->pluck('name', 'id')->toArray(),
                    // },
                    'options' => $field->mechanicOptions($mechanic),
                    'type' => match (get_class($parent)) {
                        default => 'unknown',
                        App\Models\PlayerClass::class => 'class',
                        App\Models\Race::class => 'race',
                        App\Models\Background::class => 'bg',
                        App\Models\Feat::class => 'feat',
                    },
                    'id' => $parent->id,
                    'value' => $allMechanics[$feature->id.$mechanic['grant'].$id] ?? null,
                ], key($feature->name.'-'.$mechanic['grant'].$id))
            @endif
        @endforeach
    </x-filament::section>
</x-dynamic-component>
