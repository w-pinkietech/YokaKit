@props(['name', 'label', 'required' => null, 'init' => null])
@php
$config = [
    'color' => $init,
    'extensions' => [
        [
            'name' => 'swatches',
            'options' => [
                'colors' => [
                    // 1行目
                    'white' => '#ffffff',
                    'black' => '#000000',
                    'primary' => '#007bff',
                    'info' => '#17a2b8',
                    'success' => '#28a745',
                    'warning' => '#ffc107',
                    'danger' => '#dc3545',
                    // 2行目
                    'gray' => '#adb5bd',
                    'secondary' => '#6c757d',
                    'primary2' => '#337ab7',
                    'info2' => '#5bc0de',
                    'success2' => '#5cb85c',
                    'warning2' => '#f0ad4e',
                    'danger2' => '#d9534f',
                    // 3行目
                    'indigo' => '#6610f2',
                    'navy' => '#001f3f',
                    'purple' => '#605ca8',
                    'lightblue' => '#3c8dbc',
                    'olive' => '#3d9970',
                    'orange' => '#ff851b',
                    'fuchsia' => '#f012be',
                ],
                'namesAsValues' => false,
            ],
        ],
    ],
];
@endphp
<x-adminlte-input-color name="{{ $name }}" data-format="hex" label="{{ $label }}" :config="$config"
    fgroup-class="{{ is_null($required) ? '' : 'required' }}" label-class="{{ is_null($required) ? '' : 'control-label' }}" enable-old-support>
    <x-slot name="prependSlot">
        <div class="input-group-text bg-light">
            <i class="fas fa-fw fa-paintbrush"></i>
        </div>
    </x-slot>
</x-adminlte-input-color>
