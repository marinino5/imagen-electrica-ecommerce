<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.head')
    {{-- Aplica la barra contraída antes de pintar la página, para que no "salte" --}}
    <script>
        try {
            if (localStorage.getItem('admin.sidebar.collapsed') === '1') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        } catch (e) {}
    </script>
</head>
<body class="admin-body">
    @php($user = auth()->user())

    <div class="d-lg-flex">
        {{-- Barra lateral: fija y contraíble en escritorio, menú deslizable en celular --}}
        <aside class="admin-sidebar offcanvas-lg offcanvas-start" id="admin-sidebar" tabindex="-1" aria-label="Menú principal">
            {{-- No se usa .offcanvas-header: Bootstrap lo oculta en escritorio --}}
            <div class="sidebar-header d-flex align-items-center px-3 pt-4 pb-2">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                    <img src="{{ asset('images/logo-blanco.png') }}" alt="Imagen Eléctrica" class="sidebar-logo">
                    <small class="sidebar-brand-text">Panel administrativo</small>
                </a>
                <button type="button" class="btn-close btn-close-white d-lg-none ms-auto" data-bs-dismiss="offcanvas" data-bs-target="#admin-sidebar" aria-label="Cerrar menú"></button>
            </div>

            <div class="offcanvas-body px-3 pb-3">
                @include('admin.partials.sidebar')

                <div class="sidebar-user">
                    <span class="avatar" aria-hidden="true" title="{{ $user->name }}">{{ $user->initials() }}</span>
                    <div class="sidebar-user-info">
                        <div class="name">{{ $user->name }}</div>
                        <span class="role-badge role-badge-{{ $user->role->value }}">{{ $user->role->label() }}</span>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}" class="sidebar-logout">
                        @csrf
                        <button type="submit" class="btn btn-link sidebar-logout p-1" title="Cerrar sesión" aria-label="Cerrar sesión">
                            <i class="bi bi-box-arrow-right fs-5" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="admin-main flex-grow-1 d-flex flex-column min-vh-100">
            <header class="admin-topbar">
                {{-- Celular: abre el menú. Escritorio: contrae o expande la barra --}}
                <button class="topbar-icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-sidebar" aria-controls="admin-sidebar" aria-label="Abrir menú">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
                <button class="topbar-icon-btn d-none d-lg-inline-flex" type="button" data-sidebar-toggle aria-label="Contraer o expandir el menú" title="Contraer o expandir el menú">
                    <i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i>
                </button>

                <nav aria-label="Ruta de navegación" class="min-w-0">
                    <ol class="breadcrumb mb-0 flex-nowrap">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-door" aria-hidden="true"></i><span class="visually-hidden">Inicio</span></a>
                        </li>
                        @yield('breadcrumb')
                    </ol>
                </nav>

                {{-- Buscador global: por ahora busca productos --}}
                <form method="GET" action="{{ route('admin.search') }}" role="search" class="topbar-search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <label for="global-search" class="visually-hidden">Buscar productos</label>
                    <input type="search" id="global-search" name="q" value="{{ request()->routeIs('admin.search') ? request('q') : '' }}"
                        class="form-control" placeholder="Buscar productos por nombre o referencia…" maxlength="100" autocomplete="off">
                </form>

                <div class="ms-auto ms-lg-0 d-flex align-items-center gap-3">
                    <div class="topbar-greeting d-none d-xl-block">
                        <strong>{{ $greeting }}, {{ \Illuminate\Support\Str::before($user->name, ' ') }}</strong><br>
                        <small>{{ $todayLabel }}</small>
                    </div>

                    <div class="dropdown">
                        <button class="topbar-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                            aria-label="Notificaciones: {{ $newQuotesCount }} cotizaciones nuevas">
                            <i class="bi bi-bell" aria-hidden="true"></i>
                            @if ($newQuotesCount > 0)
                                <span class="topbar-count">{{ $newQuotesCount > 99 ? '99+' : $newQuotesCount }}</span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end bell-menu p-0">
                            <div class="px-3 py-2 border-bottom fw-semibold">Notificaciones</div>
                            @if ($newQuotesCount > 0)
                                <a class="dropdown-item d-flex gap-2 py-3 text-wrap" href="{{ route('admin.dashboard') }}#atencion">
                                    <i class="bi bi-file-earmark-text text-primary fs-5" aria-hidden="true"></i>
                                    <span>
                                        <strong>{{ $newQuotesCount }} {{ $newQuotesCount === 1 ? 'cotización nueva' : 'cotizaciones nuevas' }}</strong><br>
                                        <small class="text-body-secondary">Esperan respuesta del equipo comercial.</small>
                                    </span>
                                </a>
                            @else
                                <x-empty-state icon="bell-slash" title="Todo al día" message="No hay cotizaciones nuevas." class="py-3" />
                            @endif
                        </div>
                    </div>

                    <div class="dropdown">
                        <button class="topbar-avatar" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menú de {{ $user->name }}">
                            <span class="avatar" aria-hidden="true">{{ $user->initials() }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="px-3 py-2">
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <span class="role-badge role-badge-{{ $user->role->value }}">{{ $user->role->label() }}</span>
                            </div>
                            <hr class="dropdown-divider">
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Cerrar sesión</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-grow-1 p-3 p-lg-4">
                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.toasts')
</body>
</html>
