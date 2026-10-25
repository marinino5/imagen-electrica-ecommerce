{{--
    Plantilla de las páginas de error. Laravel también la usa para los errores
    que no tienen vista propia (419, 429, 503...), así todos llevan la marca.
--}}
@extends('layouts.minimal')

@section('content')
    <div class="text-center">
        <div class="error-code mb-2">@yield('code')</div>
        <h1 class="h4 mb-2">@yield('title')</h1>
        <p class="text-body-secondary mb-4">@yield('message')</p>
        <a href="{{ url('/admin') }}" class="btn btn-primary">Volver al panel</a>
    </div>
@endsection
