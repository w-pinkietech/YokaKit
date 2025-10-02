@php
$dateRangeConfig = [
    'singleDatePicker' => true,
    'showDropdowns' => true,
    'autoApply' => true,
    'datePicker' => false,
    'timePicker' => true,
    'timePicker24Hour' => true,
    'timePickerIncrement' => 5,
    'applyButtonClasses' => 'btn-primary',
    'cancelButtonClasses' => 'btn-danger',
    'language' => 'ja',
    'locale' => ['format' => 'HH:mm'],
];
@endphp

<x-adminlte-date-range id="{{ $name }}" name="{{ $name }}" value="{{ $value }}" label="{{ $label }}" :config="$dateRangeConfig"
    fgroup-class="required" label-class="control-label" enable-old-support>
    <x-slot name="prependSlot">
        <div class="input-group-text bg-light">
            <i class="fa-solid fa-fw fa-clock"></i>
        </div>
    </x-slot>
</x-adminlte-date-range>
