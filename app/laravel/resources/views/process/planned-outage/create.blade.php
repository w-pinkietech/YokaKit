@extends('components.header', ['breadcrumbs' => $process])

@section('title', __('yokakit.target_add', ['target' => __('yokakit.process_planned_outage')]))

@section('content')
    <x-form-create action="{{ route('process.planned-outage.store', ['process' => $process]) }}"
        back="{{ route('process.show', ['process' => $process, 'tab' => 'planned-outage']) }}">
        <x-select name="planned_outage_id" label="{{ __('yokakit.planned_outage') }}" :options="$plannedOutages" icon="stop" required />
    </x-form-create>
@endsection
