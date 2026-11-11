{{-- Pestañas de un grupo del menú. Uso: @include('admin.partials.tabs', ['group' => 'ventas']) --}}
@php
    $isAdmin = auth()->user()->hasRole(\App\Enums\UserRole::Admin);
    // [texto, ruta, patrón de rutas activas]
    $groups = [
        'ventas' => [
            ['Pedidos', route('admin.orders.index'), 'admin.orders.*'],
            ['Cotizaciones', route('admin.quotes.index'), 'admin.quotes.*|admin.quote-requests.*'],
            ['Fichas descargadas', route('admin.datasheets.index'), 'admin.datasheets.*'],
        ],
        'catalogo' => [
            ['Productos', route('admin.products.index'), 'admin.products.*'],
            ['Familias y categorías', route('admin.categories.index'), 'admin.categories.*'],
            ['Marcas', route('admin.brands.index'), 'admin.brands.*'],
            ['Promociones', route('admin.promotions.index'), 'admin.promotions.*'],
        ],
        'clientes' => [
            ['Clientes', route('admin.customers.index'), 'admin.customers.*'],
            ['Suscriptores', route('admin.newsletter.index'), 'admin.newsletter.*'],
        ],
        'analisis' => [
            ['Financiero', route('admin.reports.index'), 'admin.reports.index|admin.reports.export'],
            ['Comercial', route('admin.reports.commercial'), 'admin.reports.commercial'],
        ],
    ];
@endphp
<nav class="panel-tabs" aria-label="Secciones de {{ $group }}">
    @foreach ($groups[$group] as [$label, $url, $pattern])
        @php($on = request()->routeIs(...explode('|', $pattern)))
        <a href="{{ $url }}" @class(['panel-tab', 'is-on' => $on]) @if ($on) aria-current="page" @endif>{{ $label }}</a>
    @endforeach
</nav>
