@extends('components.header', ['breadcrumbs' => $partNumber])

@section('title', __('yokakit.target_edit', ['target' => __('yokakit.part_number')]))

@section('content')
    <x-form-edit action="{{ route('part-number.update', ['partNumber' => $partNumber]) }}" back="{{ route('part-number.index') }}">
        <x-input name="part_number_name" value="{!! $partNumber->part_number_name !!}" label="{{ __('yokakit.target_name', ['target' => __('yokakit.part_number')]) }}"
            icon="hammer" required />
        <x-input name="barcode" value="{!! $partNumber->barcode !!}" label="{{ __('yokakit.barcode') }}" icon="barcode" />
        <x-textarea name="remark" value="{!! $partNumber->remark !!}" label="{{ __('yokakit.remark') }}" />
    </x-form-edit>
@endsection
