@extends('layouts.admin')

@section('title', 'Inicio')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Inicio</li>
@endsection

@php
    $kpis = $overview['kpis'];
    $money = fn ($value) => '$ '.number_format((float) $value, 0, ',', '.');
    $format = fn (array $k) => match ($k['format']) {
        'money' => $money($k['value']),
        'hours' => $k['value'] === null ? '—' : number_format((float) $k['value'], 1, ',', '.').' h',
        default => number_format((int) $k['value'], 0, ',', '.'),
    };
    $hrefs = [
        'sales' => auth()->user()->hasRole(\App\Enums\UserRole::Admin) ? route('admin.reports.index') : route('admin.orders.index', ['status' => 'paid']),
        'orders' => route('admin.orders.index', ['status' => 'pending_payment']),
        'quotes' => route('admin.quotes.index'),
        'response' => route('admin.quotes.index'),
    ];
    $icons = ['sales' => 'cash-coin', 'orders' => 'receipt', 'quotes' => 'file-earmark-text', 'response' => 'stopwatch'];
@endphp

@section('content')
    <div class="page-header animate-in">
        <h1>Inicio</h1>
        <p>Lo que necesita tu atención hoy en Imagen Eléctrica. Los indicadores son de <strong>{{ $overview['period']->label() }}</strong>, comparados con {{ $overview['period']->previousLabel() }}.</p>
    </div>

    {{-- Lo primero: lo que hay que atender --}}
    <div class="card mb-4 animate-in" id="atencion">
        <div class="card-body pb-0">
            <div class="card-title-row mb-3">
                <h2>Requiere tu atención</h2>
                <span class="small text-body-secondary d-none d-sm-inline">Lo atrasado primero, luego lo más antiguo</span>
            </div>
        </div>
        @if ($overview['attention']->isEmpty())
            <x-empty-state icon="check2-circle" title="Todo al día" message="No hay pedidos por atender ni cotizaciones sin responder." />
        @else
            @foreach ($overview['attention'] as $item)
                <div class="order-list-item">
                    <span class="order-icon" aria-hidden="true"><i class="bi bi-{{ $item->kind === 'order' ? 'receipt' : 'file-earmark-text' }}"></i></span>
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex justify-content-between gap-2">
                            <a href="{{ $item->url }}" class="order-code text-decoration-none">{{ $item->code }}</a>
                            <span @class(['hours-badge', 'is-overdue' => $item->overdue])>
                                @if ($item->overdue)
                                    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                                    <span class="visually-hidden">Atrasada:</span>
                                @endif
                                {{ $item->hours }} h
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center gap-2 mt-1">
                            <small class="text-body-secondary text-truncate">{{ $item->client }} · {{ $item->status_label }}</small>
                            <span class="d-flex gap-2 flex-shrink-0">
                                @if ($item->can_confirm_payment)
                                    <a href="{{ $item->url }}" class="btn btn-sm btn-primary">Confirmar pago</a>
                                @endif
                                <a href="{{ $item->url }}" class="btn btn-sm btn-outline-primary">{{ $item->kind === 'quote' ? 'Responder' : 'Ver' }}</a>
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Cuatro indicadores del período, con su variación y su minigráfica --}}
    <div class="row g-3 mb-4">
        @foreach ($kpis as $key => $kpi)
            @php
                $change = $kpi['change'];
                $good = $kpi['lower_is_better'] ? $change < 0 : $change > 0;
            @endphp
            <div class="col-12 col-sm-6 col-xl-3 animate-in" style="--delay: {{ $loop->index * 60 }}ms">
                <div class="card stat-card kpi-card is-link position-relative h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <div class="stat-label">
                                <a href="{{ $hrefs[$key] }}" class="stretched-link text-reset text-decoration-none">{{ $kpi['label'] }}</a>
                            </div>
                            <span class="stat-icon stat-icon-sm" aria-hidden="true"><i class="bi bi-{{ $icons[$key] }}"></i></span>
                        </div>
                        <div class="kpi-main">
                            <div class="stat-value">{{ $format($kpi) }}</div>
                            <x-sparkline :values="$kpi['series']" :label="'Tendencia de '.mb_strtolower($kpi['label']).' en el período'" />
                        </div>
                        <div class="stat-trend">
                            @if ($change === null)
                                <span class="trend-chip trend-flat">Sin base para comparar</span>
                            @else
                                {{-- Verde si el cambio es bueno y rojo si es malo; la flecha indica la dirección --}}
                                <span @class(['trend-chip', 'trend-up' => $change != 0 && $good, 'trend-down' => $change != 0 && ! $good, 'trend-flat' => $change == 0])>
                                    <i @class(['bi', 'bi-arrow-up-right' => $change > 0, 'bi-arrow-down-right' => $change < 0, 'bi-dash' => $change == 0]) aria-hidden="true"></i>
                                    {{ $change > 0 ? '+' : '' }}{{ number_format($change, 1, ',', '.') }} %
                                </span>
                                <span class="visually-hidden">frente al período anterior</span>
                            @endif
                            <span class="kpi-note">{{ $kpi['note'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Ventas y cotizaciones del período, y el embudo cotización → pedido --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title-row mb-3">
                        <h2>Ventas y cotizaciones</h2>
                        @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin))
                            <a href="{{ route('admin.reports.index') }}" class="small text-nowrap">Ver análisis</a>
                        @endif
                    </div>
                    <p class="small text-body-secondary mb-2">Pasa el cursor sobre la gráfica para ver el día; toca un nombre de la leyenda para ocultar o mostrar una serie.</p>
                    <div class="chart-box">
                        <canvas data-chart="sales" data-chart-data='@json($overview['chart'])'
                            role="img" aria-label="Gráfica de las ventas en pesos y las cotizaciones recibidas en el período"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title-row mb-3"><h2>Embudo de cotizaciones</h2></div>
                    @php($top = max(1, $overview['funnel'][0]['value']))
                    <ol class="funnel" aria-label="Embudo de cotización a pedido">
                        @foreach ($overview['funnel'] as $stage)
                            <li class="funnel-stage" style="--w: {{ max(4, round($stage['value'] / $top * 100)) }}%">
                                <div class="funnel-head">
                                    <span>{{ $stage['label'] }}</span>
                                    <strong>{{ number_format($stage['value'], 0, ',', '.') }}</strong>
                                </div>
                                <div class="funnel-bar" aria-hidden="true"><span></span></div>
                                <small class="text-body-secondary">
                                    @if ($stage['of_previous'] === null)
                                        Punto de partida
                                    @else
                                        {{ number_format($stage['of_previous'], 1, ',', '.') }} % de la etapa anterior
                                    @endif
                                </small>
                            </li>
                        @endforeach
                    </ol>
                    <p class="small text-body-secondary mb-0">Las cotizaciones sin cuenta se cierran fuera de la tienda: cuentan como recibidas, pero no pasan a «aceptadas».</p>
                </div>
            </div>
        </div>
    </div>
@endsection
