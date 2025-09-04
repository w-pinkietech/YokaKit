@extends('components.header')

@section('title', __('yokakit.target_add', ['target' => __('yokakit.part_number')]))

@section('content')
    <x-form-create action="{{ route('part-number.store') }}" back="{{ route('part-number.index') }}">
        <x-input name="part_number_name" label="{{ __('yokakit.target_name', ['target' => __('yokakit.part_number')]) }}" icon="hammer" required />
        <x-input name="barcode" label="{{ __('yokakit.barcode') }}" icon="barcode" />
        <x-textarea name="remark" label="{{ __('yokakit.remark') }}" />
    </x-form-create>
@endsection
