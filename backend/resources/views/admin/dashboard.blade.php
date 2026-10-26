@extends('layouts.admin')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@use('App\Enums\UserRole')
@use('App\Services\DashboardService')

@php
    $isAdmin = auth()->user()->hasRole(UserRole::Admin);
    $responseHours = DashboardService::QUOTE_RESPONSE_HOURS;

    // Cada indicador abre su detalle con el filtro ya aplicado. Los productos
    // solo los administra el rol admin: para el comercial esas tarjetas no enlazan.
    $links = [
        'pending_payment' => route('admin.orders.index', ['type' => 'purchase', 'status' => 'pending_payment']),
        'new_quotes' => route('admin.orders.index', ['type' => 'quote', 'status' => 'new']),
        'unanswered_quotes' => route('admin.orders.index', ['type' => 'quote', 'status' => 'new,in_review']),
        'overdue' => route('admin.orders.index', ['overdue' => 1]),
        'purchases' => route('admin.orders.index', ['type' => 'purchase']),
        'all_orders' => route('admin.orders.index'),
        'active_products' => $isAdmin ? route('admin.products.index', ['status' => 'active']) : null,
        // Mismo criterio que la tarjeta "Stock bajo": activos, de venta directa y con poco stock
        'low_stock' => $isAdmin ? route('admin.products.index', ['stock' => 'low', 'sale_mode' => 'direct', 'status' => 'active']) : null,
    ];

    $cards = [
        ['data' => $summary['pending_payment_orders'], 'label' => 'Pedidos pendientes de pago', 'icon' => 'credit-card', 'color' => 'primary', 'href' => $links['pending_payment']],
        ['data' => $summary['new_quotes'], 'label' => 'Cotizaciones nuevas', 'icon' => 'file-earmark-text', 'color' => 'accent', 'href' => $links['new_quotes']],
        ['data' => $summary['active_products'], 'label' => 'Productos activos', 'icon' => 'box-seam', 'color' => 'ink', 'href' => $links['active_products']],
        ['data' => $summary['overdue_quotes'], 'label' => "Cotizaciones con más de {$responseHours} h sin respuesta", 'icon' => 'alarm', 'color' => 'danger', 'href' => $links['overdue']],
    ];

    $quickLinks = [
        ['href' => $links['unanswered_quotes'], 'icon' => 'hourglass-split', 'title' => 'Requieren atención', 'text' => 'Cotizaciones sin responder'],
        ['href' => $links['all_orders'], 'icon' => 'inboxes', 'title' => 'Solicitudes', 'text' => 'Pedidos y cotizaciones'],
    ];
    if ($isAdmin) {
        $quickLinks[] = ['href' => route('admin.products.index'), 'icon' => 'box-seam', 'title' => 'Productos', 'text' => 'Precios, stock y fichas'];
        $quickLinks[] = ['href' => route('admin.categories.index'), 'icon' => 'diagram-3', 'title' => 'Categorías', 'text' => 'Líneas de producto'];
        $quickLinks[] = ['href' => route('admin.brands.index'), 'icon' => 'award', 'title' => 'Marcas', 'text' => 'Fabricantes del catálogo'];
    } else {
        $quickLinks[] = ['href' => $links['purchases'], 'icon' => 'receipt', 'title' => 'Pedidos', 'text' => 'Compras de la tienda'];
    }

    $money = fn ($value) => $value === null ? 'Por definir' : '$ '.number_format((float) $value, 0, ',', '.');
@endphp

@section('content')
    <div class="page-header animate-in">
        <h1>Dashboard</h1>
        <p>Resumen de solicitudes y catálogo de Imagen Eléctrica.</p>
    </div>

    {{-- Indicadores --}}
    <div class="row g-3 mb-4">
        @foreach ($cards as $i => $card)
            <div class="col-12 col-sm-6 col-xxl-3 animate-in" style="--delay: {{ $i * 60 }}ms">
                <div @class(['card stat-card position-relative', 'stat-card-'.$card['color'], 'is-link' => $card['href']])>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-value">{{ number_format($card['data']['value'], 0, ',', '.') }}</div>
                            <span class="stat-icon" aria-hidden="true"><i class="bi bi-{{ $card['icon'] }}"></i></span>
                        </div>
                        <div class="stat-label mb-2">
                            @if ($card['href'])
                                <a href="{{ $card['href'] }}" class="stretched-link text-reset text-decoration-none">{{ $card['label'] }}</a>
                            @else
                                {{ $card['label'] }}
                            @endif
                        </div>

                        <div class="stat-trend">
                            @isset($card['data']['this_week'])
                                {{-- Comparación: creados en los últimos 7 días frente a los 7 anteriores --}}
                                @php($delta = $card['data']['this_week'] - $card['data']['last_week'])
                                <span @class(['trend-chip', 'trend-up' => $delta > 0, 'trend-down' => $delta < 0, 'trend-flat' => $delta === 0])>
                                    <i @class(['bi', 'bi-arrow-up-right' => $delta > 0, 'bi-arrow-down-right' => $delta < 0, 'bi-dash' => $delta === 0]) aria-hidden="true"></i>
                                    {{ $delta > 0 ? '+' : '' }}{{ $delta }}
                                </span>
                                vs. semana anterior ({{ $card['data']['this_week'] }} esta semana)
                            @elseif ($card['data']['oldest_hours'] !== null)
                                La más antigua lleva <strong>{{ $card['data']['oldest_hours'] }} h</strong> esperando
                            @else
                                Sin cotizaciones pendientes
                            @endisset
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Gráficos --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title-row mb-3">
                        <h2>Solicitudes por estado</h2>
                        <a href="{{ $links['all_orders'] }}" class="small">Ver solicitudes</a>
                    </div>
                    @if (count($summary['status_chart']['data']) > 0)
                        <div class="chart-box">
                            <canvas data-chart="status" data-chart-data='@json($summary['status_chart'])'
                                role="img" aria-label="Gráfico de dona de pedidos y cotizaciones por estado"></canvas>
                        </div>
                    @else
                        <x-empty-state icon="pie-chart" title="Aún no hay solicitudes" message="El gráfico aparecerá con el primer pedido o cotización." />
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title-row mb-3">
                        <h2>Últimos {{ DashboardService::DAILY_CHART_DAYS }} días</h2>
                        <a href="{{ $links['all_orders'] }}" class="small">Ver solicitudes</a>
                    </div>
                    <div class="chart-box">
                        <canvas data-chart="daily" data-chart-data='@json($summary['daily_chart'])'
                            role="img" aria-label="Gráfico de barras de solicitudes recibidas en los últimos {{ DashboardService::DAILY_CHART_DAYS }} días"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Cotizaciones que requieren atención --}}
        <div class="col-12 col-xl-7" id="atencion">
            <div class="card h-100">
                <div class="card-body pb-0">
                    <div class="card-title-row mb-3">
                        <h2>Requieren atención</h2>
                        <span class="small text-body-secondary d-none d-sm-inline">Sin respuesta, la más antigua primero</span>
                        <a href="{{ $links['unanswered_quotes'] }}" class="small text-nowrap">Ver todas</a>
                    </div>
                </div>
                @if ($summary['attention_quotes']->isEmpty())
                    <x-empty-state icon="check2-circle" title="Todo al día" message="No hay cotizaciones esperando respuesta." />
                @else
                    <div class="table-responsive">
                        <table class="table table-dashboard">
                            <thead>
                                <tr>
                                    <th scope="col">Código</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col" class="d-none d-sm-table-cell">Estado</th>
                                    <th scope="col" class="text-end">Sin respuesta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($summary['attention_quotes'] as $item)
                                    <tr>
                                        <td><a href="{{ route('admin.orders.show', $item['order']) }}" class="order-code text-decoration-none">{{ $item['order']->code }}</a></td>
                                        <td>
                                            <div class="fw-semibold">{{ $item['order']->display_name }}</div>
                                            <small class="text-body-secondary">{{ $item['order']->customer_type->requiresCompanyData() ? $item['order']->contact_name : $item['order']->customer_type->label() }}</small>
                                        </td>
                                        <td class="d-none d-sm-table-cell"><x-status-badge :status="$item['order']->status" /></td>
                                        <td class="text-end">
                                            <span @class(['hours-badge', 'is-overdue' => $item['is_overdue']])>
                                                @if ($item['is_overdue'])
                                                    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                                                    <span class="visually-hidden">Atrasada:</span>
                                                @endif
                                                {{ $item['hours_waiting'] }} h
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Últimos pedidos --}}
        <div class="col-12 col-xl-5" id="ultimos-pedidos">
            <div class="card h-100">
                <div class="card-body pb-2">
                    <div class="card-title-row">
                        <h2>Últimos pedidos</h2>
                        <a href="{{ $links['purchases'] }}" class="small text-nowrap">Ver todos</a>
                    </div>
                </div>
                @forelse ($summary['latest_orders'] as $order)
                    <a href="{{ route('admin.orders.show', $order) }}" class="order-list-item text-reset text-decoration-none">
                        <span class="order-icon" aria-hidden="true"><i class="bi bi-receipt"></i></span>
                        <div class="min-w-0 flex-grow-1">
                            <div class="d-flex justify-content-between gap-2">
                                <span class="order-code">{{ $order->code }}</span>
                                <strong>{{ $money($order->total) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center gap-2 mt-1">
                                <small class="text-body-secondary text-truncate">{{ $order->display_name }} · {{ $order->created_at->locale('es')->diffForHumans() }}</small>
                                <x-status-badge :status="$order->status" />
                            </div>
                        </div>
                    </a>
                @empty
                    <x-empty-state icon="receipt" title="Sin pedidos todavía" message="Los pedidos de la tienda aparecerán aquí." />
                @endforelse
            </div>
        </div>
    </div>

    {{-- Accesos rápidos --}}
    <section class="mb-4" aria-labelledby="accesos-rapidos">
        <div class="card-title-row mb-3">
            <h2 id="accesos-rapidos" class="h5">Accesos rápidos</h2>
        </div>
        <div class="row g-3">
            @foreach ($quickLinks as $link)
                <div class="col-12 col-sm-6 col-xl-3">
                    <a href="{{ $link['href'] }}" class="quick-link">
                        <span class="quick-link-icon" aria-hidden="true"><i class="bi bi-{{ $link['icon'] }}"></i></span>
                        <span>
                            <strong>{{ $link['title'] }}</strong>
                            <small>{{ $link['text'] }}</small>
                        </span>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Stock bajo: productos de venta directa con 5 unidades o menos, y agotados --}}
    <section aria-labelledby="stock-bajo">
        <div class="card">
            <div class="card-body pb-2">
                <div class="card-title-row">
                    <h2 id="stock-bajo">Stock bajo</h2>
                    <span class="small text-body-secondary">
                        Productos con {{ DashboardService::LOW_STOCK_THRESHOLD }} unidades o menos
                        @if ($summary['low_stock']['total'] > 0)
                            · {{ $summary['low_stock']['total'] }} en total
                        @endif
                    </span>
                </div>
            </div>

            @forelse ($summary['low_stock']['products'] as $product)
                {{-- Solo admin puede abrir la ficha del producto --}}
                <{{ $isAdmin ? 'a' : 'div' }} class="stock-item" @if ($isAdmin) href="{{ route('admin.products.show', $product) }}" @endif>
                    <div class="min-w-0">
                        <div class="stock-item-name text-truncate">{{ $product->name }}</div>
                        <div class="stock-item-sku">{{ $product->sku }}</div>
                    </div>
                    @if ($product->stock <= 0)
                        <span class="stock-badge is-out"><i class="bi bi-x-octagon-fill" aria-hidden="true"></i> Agotado</span>
                    @else
                        <span class="stock-badge"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i> Quedan {{ $product->stock }}</span>
                    @endif
                </{{ $isAdmin ? 'a' : 'div' }}>
            @empty
                <x-empty-state icon="check2-circle" title="Inventario al día" message="Ningún producto está agotado ni cerca de agotarse." />
            @endforelse

            {{-- Se listan 5; el resto está en la lista de productos con el filtro ya aplicado --}}
            @if ($links['low_stock'] && $summary['low_stock']['total'] > 0)
                <div class="card-body border-top py-2 text-end">
                    <a href="{{ $links['low_stock'] }}" class="fw-semibold">Ver todos ({{ $summary['low_stock']['total'] }}) <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
            @endif
        </div>
    </section>
@endsection
