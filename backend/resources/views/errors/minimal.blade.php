{{--
    Plantilla de las páginas de error. Laravel también la usa para los errores
    que no tienen vista propia (419, 429, 503...), así todos llevan la marca.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.head')
</head>
<body>
    <main class="error-page d-flex align-items-center justify-content-center p-3">
        @include('partials.brand-arc', ['class' => 'error-arc'])

        <div class="error-card card shadow animate-in">
            <div class="card-body p-4 p-sm-5 text-center">
                <span class="logo-plate error-logo mb-4"><img src="{{ asset('images/logo.png') }}" alt="Imagen Eléctrica"></span>
                <div class="error-code mb-2">@yield('code')</div>
                <h1 class="h3 text-uppercase mb-2">@yield('title')</h1>
                <p class="text-body-secondary mb-4">@yield('message')</p>
                <a href="{{ url('/admin') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Volver al panel
                </a>
            </div>
        </div>
    </main>
</body>
</html>
