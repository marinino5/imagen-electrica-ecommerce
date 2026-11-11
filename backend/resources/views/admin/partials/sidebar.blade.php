@php
    $isAdmin = auth()->user()->hasRole(\App\Enums\UserRole::Admin);

    // Cinco grupos como máximo: lo importante primero; el resto vive en pestañas dentro de cada grupo.
    // [ruta, patrón para marcar activo, ícono, texto]
    $links = [
        ['admin.dashboard', 'admin.dashboard', 'grid-1x2', 'Inicio'],
        ['admin.orders.index', 'admin.orders.*|admin.quotes.*|admin.quote-requests.*|admin.datasheets.*', 'inboxes', 'Ventas'],
    ];

    // El catálogo y el análisis (con costos) solo los ve el rol admin
    if ($isAdmin) {
        $links[] = ['admin.products.index', 'admin.products.*|admin.categories.*|admin.brands.*|admin.promotions.*', 'box-seam', 'Catálogo'];
    }

    $links[] = ['admin.customers.index', 'admin.customers.*|admin.newsletter.*', 'people', 'Clientes'];

    if ($isAdmin) {
        $links[] = ['admin.reports.index', 'admin.reports.*', 'graph-up-arrow', 'Análisis'];
    }
@endphp

<nav class="flex-grow-1" aria-label="Secciones del panel">
    @foreach ($links as [$route, $pattern, $icon, $label])
        @php($active = request()->routeIs(...explode('|', $pattern)))
        <a href="{{ route($route) }}" @class(['sidebar-link', 'active' => $active]) @if ($active) aria-current="page" @endif
            data-sidebar-tooltip data-bs-title="{{ $label }}">
            <i class="bi bi-{{ $icon }}" aria-hidden="true"></i>
            <span class="sidebar-link-label">{{ $label }}</span>
        </a>
    @endforeach
</nav>
