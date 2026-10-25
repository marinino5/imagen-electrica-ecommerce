@php
    $isAdmin = auth()->user()->hasRole(\App\Enums\UserRole::Admin);

    // [ruta, patrón para marcar activo, ícono, texto]
    $sections = [
        'General' => [
            ['admin.dashboard', 'admin.dashboard', 'grid-1x2', 'Dashboard'],
            ['admin.orders.index', 'admin.orders.*', 'inboxes', 'Solicitudes'],
        ],
    ];

    // El catálogo y los reportes (con costos) solo los ve el rol admin
    if ($isAdmin) {
        $sections['Catálogo'] = [
            ['admin.products.index', 'admin.products.*', 'box-seam', 'Productos'],
            ['admin.categories.index', 'admin.categories.*', 'diagram-3', 'Categorías'],
            ['admin.brands.index', 'admin.brands.*', 'award', 'Marcas'],
        ];
        $sections['Análisis'] = [
            ['admin.reports.index', 'admin.reports.*', 'graph-up-arrow', 'Reportes'],
        ];
    }
@endphp

<nav class="flex-grow-1" aria-label="Secciones del panel">
    @foreach ($sections as $section => $links)
        <div class="sidebar-section">{{ $section }}</div>
        @foreach ($links as [$route, $pattern, $icon, $label])
            @php($active = request()->routeIs($pattern))
            <a href="{{ route($route) }}" @class(['sidebar-link', 'active' => $active]) @if ($active) aria-current="page" @endif
                data-sidebar-tooltip data-bs-title="{{ $label }}">
                <i class="bi bi-{{ $icon }}" aria-hidden="true"></i>
                <span class="sidebar-link-label">{{ $label }}</span>
            </a>
        @endforeach
    @endforeach
</nav>
