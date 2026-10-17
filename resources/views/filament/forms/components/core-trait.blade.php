<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $key = $getKey();
        $classId = $field->classId;
        // dd($classId);
    @endphp
</x-dynamic-component>
