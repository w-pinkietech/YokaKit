@props(['name' => '', 'label', 'checked' => false, 'on' => 'YES', 'off' => 'NO'])

@if ($checked)
    <x-adminlte-input-switch id="{{ $name }}" name="{{ $name }}" data-on-text="{{ $on }}"
        data-off-text="{{ $off }}" data-on-color="info" label="{{ $label }}" enable-old-support checked />
@else
    <x-adminlte-input-switch id="{{ $name }}" name="{{ $name }}" data-on-text="{{ $on }}"
        data-off-text="{{ $off }}" data-on-color="info" label="{{ $label }}" enable-old-support />
@endif
