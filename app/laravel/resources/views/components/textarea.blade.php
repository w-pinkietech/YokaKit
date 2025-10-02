@props(['name', 'label', 'value' => null])

<x-adminlte-textarea name="{{ $name }}" label="{{ $label }}" rows="10" enable-old-support>
    {{ $value }}
</x-adminlte-textarea>
