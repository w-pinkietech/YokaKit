@extends('adminlte::page')

@section('content_header')
    <div class="row">
        <div class="col-sm-4">
            <h1 class="text-dark m-0">
                @yield('title')
            </h1>
        </div>
        <div class="col-sm-8">
            <div class="float-right">
                @isset($breadcrumbs)
                    {{ Breadcrumbs::render(Route::currentRouteName(), $breadcrumbs) }}
                @else
                    {{ Breadcrumbs::render(Route::currentRouteName()) }}
                @endisset
            </div>
        </div>
    </div>
@endsection
