@props(['name' => '', 'label', 'value' => null, 'type' => null, 'required' => null, 'icon' => null, 'disabled' => null])

@php
    $fgroup = is_null($required) ? '' : 'required';
    $control = is_null($required) ? '' : 'control-label';
@endphp

@isset($disabled)
    <x-adminlte-input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{!! $value !!}"
        label="{{ $label }}" fgroup-class="{{ $fgroup }}" label-class="{{ $control }}" disabled="disabled">
        @isset($icon)
            <x-slot name="prependSlot">
                <div class="input-group-text bg-light">
                    @if ($icon == 'raspberry-pi')
                        <i class="fab fa-fw fa-{{ $icon }}"></i>
                    @else
                        <i class="fa-solid fa-fw fa-{{ $icon }}"></i>
                    @endif
                </div>
            </x-slot>
        @endisset
    </x-adminlte-input>
@else
    <x-adminlte-input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{!! $value !!}"
        label="{{ $label }}" fgroup-class="{{ $fgroup }}" label-class="{{ $control }}" enable-old-support>
        @isset($icon)
            <x-slot name="prependSlot">
                <div class="input-group-text bg-light">
                    @if ($icon == 'raspberry-pi')
                        <i class="fab fa-fw fa-{{ $icon }}"></i>
                    @else
                        <i class="fa-solid fa-fw fa-{{ $icon }}"></i>
                    @endif
                </div>
            </x-slot>
        @endisset
    </x-adminlte-input>
@endisset
