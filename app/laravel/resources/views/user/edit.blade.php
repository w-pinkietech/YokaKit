@extends('components.header', ['user' => $user])

@section('title', __('yokakit.target_edit', ['target' => __('yokakit.profile')]))

@section('content')
    <x-form-edit action="{{ route('user.update') }}" back="{{ route('user.show') }}">
        <x-input name="name" value="{!! $user->name !!}" label="{{ __('yokakit.name') }}" icon="user" required />
        <x-input name="email" value="{!! $user->email !!}" label="{{ __('yokakit.email') }}" icon="envelope" required />
    </x-form-edit>
@endsection
