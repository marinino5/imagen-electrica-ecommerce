{{-- Pantallas sin menú: login y páginas de error --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.head')
</head>
<body>
    <main class="minimal-page d-flex align-items-center justify-content-center p-3">
        <div class="minimal-card">
            <div class="text-center mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="Imagen Eléctrica" class="minimal-logo">
            </div>
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    @yield('content')
                </div>
            </div>
        </div>
    </main>
</body>
</html>
