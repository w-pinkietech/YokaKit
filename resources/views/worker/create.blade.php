@extends('components.header')

@section('title', __('yokakit.target_add', ['target' => __('yokakit.worker')]))

@section('content')
    <x-form-create action="{{ route('worker.store') }}" back="{{ route('worker.index') }}">
        <x-input name="identification_number" label="{{ __('yokakit.identification_number') }}" icon="id-card" required />
        <x-input name="worker_name" label="{{ __('yokakit.target_name', ['target' => __('yokakit.worker')]) }}" icon="person" required />
        <x-input name="mac_address" label="{{ __('yokakit.barcode_reader_mac_address') }}" icon="qrcode" />
    </x-form-create>
@endsection
