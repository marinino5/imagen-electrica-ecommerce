<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.head')
</head>
<body class="admin-body">
    <div class="d-lg-flex">
        {{-- Barra lateral: visible en escritorio, menú deslizable en celular --}}
        <aside class="admin-sidebar offcanvas-lg offcanvas-start" id="admin-sidebar" tabindex="-1" aria-labelledby="admin-sidebar-label">
            <div class="offcanvas-header justify-content-between justify-content-lg-center py-4">
                <a href="{{ route('admin.dashboard') }}" id="admin-sidebar-label">
                    <img src="{{ asset('images/logo.png') }}" alt="Imagen Eléctrica" class="admin-sidebar-logo">
                </a>
                <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#admin-sidebar" aria-label="Cerrar menú"></button>
            </div>
            <div class="offcanvas-body px-3 pt-0">
                @include('admin.partials.sidebar')
            </div>
        </aside>

        <div class="admin-main flex-grow-1 d-flex flex-column min-vh-100">
            <header class="admin-topbar navbar px-3 px-lg-4" data-bs-theme="dark">
                <div class="d-flex align-items-center gap-2">
                    <button class="navbar-toggler d-lg-none border-0 px-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-sidebar" aria-controls="admin-sidebar" aria-label="Abrir menú">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <h1 class="admin-page-title h5 text-white mb-0">@yield('title')</h1>
                </div>

                <div class="dropdown">
                    <button class="btn btn-link text-white text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ auth()->user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-body-secondary">{{ auth()->user()->role->label() }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Cerrar sesión</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </header>

            <main class="flex-grow-1 p-3 p-lg-4">
                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
