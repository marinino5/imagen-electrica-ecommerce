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
        <p>Lo que necesita tu atención hoy en Imagen Eléctrica.</p>
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

    {{-- Cuatro indicadores --}}
    <div class="row g-3 mb-4">
        @foreach ($kpis as $key => $kpi)
            <div class="col-12 col-sm-6 col-xl-3 animate-in" style="--delay: {{ $loop->index * 60 }}ms">
                <div class="card stat-card is-link position-relative h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="stat-value">{{ $format($kpi) }}</div>
                            <span class="stat-icon" aria-hidden="true"><i class="bi bi-{{ $icons[$key] }}"></i></span>
                        </div>
                        <div class="stat-label mb-1">
                            <a href="{{ $hrefs[$key] }}" class="stretched-link text-reset text-decoration-none">{{ $kpi['label'] }}</a>
                        </div>
                        <div class="stat-trend">
                            {{ $kpi['note'] }}
                            @if (isset($kpi['previous']) && (float) $kpi['previous'] > 0)
                                @php($delta = round(((float) $kpi['value'] - (float) $kpi['previous']) / (float) $kpi['previous'] * 100))
                                · <span @class(['trend-chip', 'trend-up' => $delta > 0, 'trend-down' => $delta < 0, 'trend-flat' => $delta == 0])>{{ $delta > 0 ? '+' : '' }}{{ $delta }} %</span> vs. mes anterior
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Una sola gráfica --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="card-title-row mb-3">
                <h2>Ventas y cotizaciones, últimos 30 días</h2>
                @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin))
                    <a href="{{ route('admin.reports.index') }}" class="small text-nowrap">Ver análisis</a>
                @endif
            </div>
            <div class="chart-box">
                <canvas data-chart="sales" data-chart-data='@json($overview['chart'])'
                    role="img" aria-label="Gráfica de las ventas en pesos y las cotizaciones recibidas por día en los últimos 30 días"></canvas>
            </div>
        </div>
    </div>
@endsection
