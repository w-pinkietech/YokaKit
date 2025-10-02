@extends('components.header')

@section('title', __('yokakit.target_add', ['target' => __('yokakit.user')]))

@section('content')
    <x-form-create action="{{ route('user.store') }}" back="{{ route('user.index') }}">
        <x-input name="name" label="{{ __('yokakit.name') }}" icon="user" required />
        <x-input name="email" label="{{ __('yokakit.email') }}" icon="envelope" required />
        <x-select name="role" label="{{ __('yokakit.role') }}" :options="$roles" icon="toolbox" required />
        <x-input name="password" type="password" label="{{ __('yokakit.password') }}" icon="lock" required />
        <x-input name="password_confirmation" type="password" label="{{ __('yokakit.password_confirmation') }}" icon="lock" required />
    </x-form-create>
@endsection
