@extends('components.header', ['breadcrumbs' => $process])

@section('title', __('yokakit.target_edit', ['target' => __('yokakit.cycle_time')]))

@section('content')
    <x-form-edit action="{{ route('cycle-time.update', ['process' => $process, 'cycleTime' => $cycleTime]) }}"
        back="{{ route('process.show', ['process' => $process, 'tab' => 'part-number']) }}">
        <x-input value="{!! $cycleTime->partNumber->part_number_name !!}" label="{{ __('yokakit.part_number') }}" icon="hammer" disabled />
        <x-input name="cycle_time" value="{{ $cycleTime->cycle_time }}" label="{{ __('yokakit.standard_cycle_time') }}{{ __('yokakit.unit_sec') }}"
            icon="stopwatch" required />
        <x-input name="over_time" value="{{ $cycleTime->over_time }}" label="{{ __('yokakit.over_time') }}{{ __('yokakit.unit_sec') }}" icon="stopwatch"
            required />
    </x-form-edit>
@endsection
