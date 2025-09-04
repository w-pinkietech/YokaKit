@extends('components.header', ['breadcrumbs' => $process])

@section('title', __('yokakit.target_add', ['target' => __('yokakit.notification')]))

@section('content')
    <x-form-create action="{{ route('onoff.store', ['process' => $process]) }}"
        back="{{ route('process.show', ['process' => $process, 'tab' => 'on-off']) }}">
        <x-input name="event_name" label="{{ __('yokakit.target_name', ['target' => __('yokakit.event')]) }}" icon="font" required />
        <x-input name="on_message" label="{{ __('yokakit.target_message', ['target' => 'ON']) }}" icon="toggle-on" required />
        <x-input name="off_message" label="{{ __('yokakit.target_message', ['target' => 'OFF']) }}" icon="toggle-off" />
        <x-select name="raspberry_pi_id" label="{{ __('yokakit.raspberry_pi') }}" :options="$raspberryPiOptions" icon="raspberry-pi" required />
        <x-select name="pin_number" label="{{ __('yokakit.pin_number') }}" :options="$pinOptions" icon="map-pin" required />
    </x-form-create>
@endsection
