@extends('components.header')

@section('title', __('yokakit.target_add', ['target' => __('yokakit.planned_outage')]))

@section('content')
    <x-form-create action="{{ route('planned-outage.store') }}" back="{{ route('planned-outage.index') }}">
        <x-input name="planned_outage_name" label="{{ __('yokakit.target_name', ['target' => __('yokakit.planned_outage')]) }}" icon="stop" required />
        <x-input-time name="start_time" value="12:00" label="{{ __('yokakit.start_time') }}" />
        <x-input-time name="end_time" value="13:00" label="{{ __('yokakit.end_time') }}" />
    </x-form-create>
@endsection
