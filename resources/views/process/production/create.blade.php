@extends('components.header', ['breadcrumbs' => $process])

@section('title', __('yokakit.switch_part_number'))

@section('content')
    <x-form-create action="{{ route('production.store', ['process' => $process]) }}" back="{{ route('process.show', ['process' => $process]) }}">
        <x-select name="part_number_id" label="{{ __('yokakit.part_number') }}" :options="$partNumbers" icon="hammer" required />
        <x-input name="goal" label="{{ __('yokakit.goal') }}" icon="bullseye" />
        <x-input-switch name="changeover" label="{{ __('yokakit.changeover') }}" checked />
    </x-form-create>
@endsection
