<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets --}}
    @if (!config('adminlte.enabled_laravel_mix'))
        <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
        <link href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}" rel="stylesheet">

        {{-- Configured Stylesheets --}}
        @include('adminlte::plugins', ['type' => 'css'])

        <link href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}" rel="stylesheet">
    @else
        <link href="{{ url(mix(config('adminlte.laravel_mix_css_path', 'css/app.css'))) }}" rel="stylesheet">
    @endif

    {{-- Livewire Styles --}}
    @if (config('adminlte.livewire'))
        @if (app()->version() >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')

    {{-- Favicon --}}
    @if (config('adminlte.use_ico_only'))
        <link href="{{ asset('favicons/favicon.ico') }}" rel="shortcut icon" />
    @elseif(config('adminlte.use_full_favicon'))
        <link href="{{ asset('favicons/favicon.ico') }}" rel="shortcut icon" />
        <link href="{{ asset('favicons/apple-icon-57x57.png') }}" rel="apple-touch-icon" sizes="57x57">
        <link href="{{ asset('favicons/apple-icon-60x60.png') }}" rel="apple-touch-icon" sizes="60x60">
        <link href="{{ asset('favicons/apple-icon-72x72.png') }}" rel="apple-touch-icon" sizes="72x72">
        <link href="{{ asset('favicons/apple-icon-76x76.png') }}" rel="apple-touch-icon" sizes="76x76">
        <link href="{{ asset('favicons/apple-icon-114x114.png') }}" rel="apple-touch-icon" sizes="114x114">
        <link href="{{ asset('favicons/apple-icon-120x120.png') }}" rel="apple-touch-icon" sizes="120x120">
        <link href="{{ asset('favicons/apple-icon-144x144.png') }}" rel="apple-touch-icon" sizes="144x144">
        <link href="{{ asset('favicons/apple-icon-152x152.png') }}" rel="apple-touch-icon" sizes="152x152">
        <link href="{{ asset('favicons/apple-icon-180x180.png') }}" rel="apple-touch-icon" sizes="180x180">
        <link type="image/png" href="{{ asset('favicons/favicon-16x16.png') }}" rel="icon" sizes="16x16">
        <link type="image/png" href="{{ asset('favicons/favicon-32x32.png') }}" rel="icon" sizes="32x32">
        <link type="image/png" href="{{ asset('favicons/favicon-96x96.png') }}" rel="icon" sizes="96x96">
        <link type="image/png" href="{{ asset('favicons/android-icon-192x192.png') }}" rel="icon" sizes="192x192">
        <link href="{{ asset('favicons/manifest.json') }}" rel="manifest" crossorigin="use-credentials">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}">
    @endif

</head>

<body class="@yield('classes_body')" @yield('body_data')>

    {{-- Body Content --}}
    @yield('body')

    {{-- Base Scripts --}}
    @if (!config('adminlte.enabled_laravel_mix'))
        <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>

        {{-- Configured Scripts --}}
        @include('adminlte::plugins', ['type' => 'js'])

        <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    @else
        <script src="{{ url(mix(config('adminlte.laravel_mix_js_path', 'js/app.js'))) }}"></script>
    @endif

    {{-- Livewire Script --}}
    @if (config('adminlte.livewire'))
        @if (app()->version() >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif

    {{-- Custom Scripts --}}
    @yield('adminlte_js')

</body>

</html>
