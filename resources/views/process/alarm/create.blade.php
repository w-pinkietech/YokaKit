@extends('components.header', ['breadcrumbs' => $process])

@section('title', __('yokakit.target_add', ['target' => __('yokakit.alarm')]))

@section('content')
    <x-form-create action="{{ route('alarm.store', ['process' => $process]) }}"
        back="{{ route('process.show', ['process' => $process, 'tab' => 'alarm']) }}">
        <x-input name="alarm_text" label="{{ __('yokakit.alarm_text') }}" icon="comment" required />
        <x-input name="identification_number" label="{{ __('yokakit.identification_number') }}" icon="0" required />
        <x-select name="raspberry_pi_id" label="{{ __('yokakit.raspberry_pi') }}" :options="$raspberryPiOptions" icon="raspberry-pi" required />
        <x-select name="sensor_type" label="{{ __('yokakit.sensor_type') }}" :options="$sensorTypes" icon="temperature-three-quarters" required />
        <x-input-switch name="trigger" label="{{ __('yokakit.trigger') }}" on="HIGH" off="LOW" />
    </x-form-create>
@endsection
