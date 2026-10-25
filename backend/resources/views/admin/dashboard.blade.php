@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        $cards = [
            ['value' => $summary['pending_payment_orders'], 'label' => 'Pedidos pendientes de pago', 'color' => 'primary'],
            ['value' => $summary['new_quotes'], 'label' => 'Cotizaciones nuevas', 'color' => 'accent'],
            ['value' => $summary['active_products'], 'label' => 'Productos activos', 'color' => 'ink'],
            ['value' => $summary['overdue_quotes'], 'label' => 'Cotizaciones con más de '.\App\Services\DashboardService::QUOTE_RESPONSE_HOURS.' h sin respuesta', 'color' => 'accent'],
        ];
    @endphp

    <div class="row g-3 mb-4">
        @foreach ($cards as $card)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card stat-card-{{ $card['color'] }} h-100">
                    <div class="card-body">
                        <div class="stat-value">{{ number_format($card['value'], 0, ',', '.') }}</div>
                        <div class="stat-label mt-2">{{ $card['label'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="h5 mb-0">Solicitudes por estado</h2>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="requests-by-status-chart" data-chart='@json($summary['chart'])'
                    role="img" aria-label="Gráfico de barras de pedidos y cotizaciones por estado"></canvas>
            </div>
        </div>
    </div>
@endsection
