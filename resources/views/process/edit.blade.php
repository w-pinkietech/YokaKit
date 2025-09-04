@extends('components.header', ['breadcrumbs' => $process])

@section('title', __('yokakit.target_edit', ['target' => __('yokakit.process')]))

@section('content')
    <x-form-edit action="{{ route('process.update', ['process' => $process]) }}" back="{{ route('process.show', ['process' => $process]) }}">
        <x-input name="process_name" value="{!! $process->process_name !!}" label="{{ __('yokakit.target_name', ['target' => __('yokakit.process')]) }}"
            icon="industry" required />
        <x-input-color name="plan_color" value="{{ $process->plan_color }}" label="{{ __('yokakit.plan_color') }}" init="{{ $process->plan_color }}"
            required />
        <x-textarea name="remark" value="{!! $process->remark !!}" label="{{ __('yokakit.remark') }}" />
    </x-form-edit>
@endsection
