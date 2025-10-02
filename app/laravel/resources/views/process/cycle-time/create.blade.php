@extends('components.header', ['breadcrumbs' => $process])

@section('title', __('yokakit.target_add', ['target' => __('yokakit.cycle_time')]))

@section('content')
    <x-form-create action="{{ route('cycle-time.store', ['process' => $process]) }}"
        back="{{ route('process.show', ['process' => $process, 'tab' => 'part-number']) }}">
        <x-select name="part_number_id" label="{{ __('yokakit.part_number') }}" :options="$partNumbers->toArray()" icon="hammer" required />
        <x-input name="cycle_time" label="{{ __('yokakit.standard_cycle_time') }}{{ __('yokakit.unit_sec') }}" icon="stopwatch" required />
        <x-input name="over_time" label="{{ __('yokakit.over_time') }}{{ __('yokakit.unit_sec') }}" icon="stopwatch" required />
    </x-form-create>
@endsection
