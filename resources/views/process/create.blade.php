@extends('components.header')

@section('title', __('yokakit.target_add', ['target' => __('yokakit.process')]))

@section('content')
    <x-form-create action="{{ route('process.store') }}" back="{{ route('process.index') }}">
        <x-input name="process_name" label="{{ __('yokakit.target_name', ['target' => __('yokakit.process')]) }}" icon="industry" required />
        <x-input-color name="plan_color" label="{{ __('yokakit.plan_color') }}" init="#FFFFFF" required />
        <x-textarea name="remark" label="{{ __('yokakit.remark') }}" />
    </x-form-create>
@endsection
