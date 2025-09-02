@extends('components.header', ['user' => $user])

@section('title', __('yokakit.change_password'))

@section('content')
    <x-form-edit action="{{ route('user.password.change') }}" back="{{ route('user.show') }}">
        <x-input name="current_password" type="password" label="{{ __('yokakit.current_password') }}" icon="lock" required />
        <x-input name="password" type="password" label="{{ __('yokakit.new_password') }}" icon="lock" required />
        <x-input name="password_confirmation" type="password" label="{{ __('yokakit.password_confirmation') }}" icon="lock" required />
    </x-form-edit>
@endsection
