@extends('layouts.admin')

@section('title', 'Reportes')

@section('breadcrumb')
    <li class="breadcrumb-item">Análisis</li>
    <li class="breadcrumb-item active" aria-current="page">Reportes</li>
@endsection

@use('App\Enums\ReportPreset')
@use('App\Support\Money')
@use('App\Services\ReportService')

@php
    $period = $report['period'];
    $indicators = $report['indicators'];
    $charts = $report['charts'];
    $tables = $report['tables'];
    $withoutCost = $report['without_cost'];

    $decimal = fn ($value) => number_format((float) $value, 1, ',', '.');
    $units = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');

    // Valor de cada indicador según su formato
    $format = fn (array $indicator, $value) => match (true) {
        $value === null => 'Sin datos',
        $indicator['format'] === 'money' => Money::cop($value),
        $indicator['format'] === 'percent' => $decimal($value).' %',
        $indicator['format'] === 'hours' => $decimal($value).' h',
        default => number_format((float) $value, 0, ',', '.'),
    };

    $cards = [
        ['key' => 'sales', 'icon' => 'cash-stack', 'color' => 'primary'],
        ['key' => 'gross_profit', 'icon' => 'graph-up-arrow', 'color' => 'accent', 'note' => true],
        ['key' => 'margin', 'icon' => 'percent', 'color' => 'ink', 'note' => true],
        ['key' => 'orders', 'icon' => 'receipt', 'color' => 'primary'],
        ['key' => 'average_ticket', 'icon' => 'tag', 'color' => 'ink'],
        ['key' => 'conversion', 'icon' => 'arrow-left-right', 'color' => 'accent'],
        ['key' => 'response_hours', 'icon' => 'stopwatch', 'color' => 'primary'],
    ];

    $hasSales = array_sum($charts['categories']['data']) > 0;
@endphp

@section('content')
    <div class="page-header animate-in d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div>
            <h1>Reportes</h1>
            <p>Ventas, ganancia y cotizaciones de <strong>{{ $period->label() }}</strong>, comparadas con {{ $period->previousLabel() }}.</p>
        </div>
        <a href="{{ route('admin.reports.export', $exportParams) }}" class="btn btn-outline-primary">
            <i class="bi bi-download me-1" aria-hidden="true"></i>Exportar CSV
        </a>
    </div>

    {{-- Filtro de periodo --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-2 align-items-end" data-report-filter>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <label for="period" class="form-label small mb-1">Periodo</label>
                    <select id="period" name="period" @class(['form-select', 'is-invalid' => $errors->has('period')]) data-report-period>
                        @foreach ($presets as $option)
                            <option value="{{ $option->value }}" @selected($preset === $option)>{{ $option->label() }}</option>
                        @endforeach
                    </select>
                    @error('period')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-md-3 col-xl-2" data-report-custom @if ($preset !== ReportPreset::Custom) hidden @endif>
                    <label for="from" class="form-label small mb-1">Desde</label>
                    <input type="date" id="from" name="from" value="{{ old('from', $period->start->toDateString()) }}"
                        @class(['form-control', 'is-invalid' => $errors->has('from')])>
                    @error('from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-md-3 col-xl-2" data-report-custom @if ($preset !== ReportPreset::Custom) hidden @endif>
                    <label for="to" class="form-label small mb-1">Hasta</label>
                    <input type="date" id="to" name="to" value="{{ old('to', $period->lastDay()->toDateString()) }}"
                        @class(['form-control', 'is-invalid' => $errors->has('to')])>
                    @error('to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Aplicar</button>
                </div>
            </form>
            <p class="small text-body-secondary mt-3 mb-0">
                Se cuentan los pedidos pagados, enviados y entregados, fechados al momento del pago. Los valores incluyen IVA;
                la ganancia y el margen se calculan sobre la base sin IVA frente al costo de cada producto.
            </p>
        </div>
    </div>

    {{-- Indicadores --}}
    <div class="row g-3 mb-4">
        @foreach ($cards as $i => $card)
            @php($indicator = $indicators[$card['key']])
            @php($change = $indicator['change'])
            <div class="col-12 col-sm-6 col-xl-4 col-xxl-3 animate-in" style="--delay: {{ $i * 60 }}ms">
                <div class="card stat-card stat-card-{{ $card['color'] }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div class="stat-value stat-value-sm">{{ $format($indicator, $indicator['value']) }}</div>
                            <span class="stat-icon" aria-hidden="true"><i class="bi bi-{{ $card['icon'] }}"></i></span>
                        </div>
                        <div class="stat-label mb-2">{{ $indicator['label'] }}</div>
                        <div class="stat-trend">
                            @if ($change === null)
                                Sin base para comparar con {{ $period->previousLabel() }}
                            @else
                                <span @class(['trend-chip', 'trend-up' => $change > 0, 'trend-down' => $change < 0, 'trend-flat' => $change == 0])>
                                    <i @class(['bi', 'bi-arrow-up-right' => $change > 0, 'bi-arrow-down-right' => $change < 0, 'bi-dash' => $change == 0]) aria-hidden="true"></i>
                                    {{ $change > 0 ? '+' : '' }}{{ $decimal($change) }} {{ in_array($indicator['format'], ['percent']) ? 'pp' : '%' }}
                                </span>
                                vs. {{ $period->previousLabel() }} ({{ $format($indicator, $indicator['previous']) }})
                            @endif
                            @if ($indicator['lower_is_better'])
                                <div>Menos horas es mejor</div>
                            @endif
                            @if (! empty($card['note']))
                                <div>
                                    @if ($withoutCost > 0)
                                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                                        Sin contar <strong>{{ $withoutCost }}</strong> {{ $withoutCost === 1 ? 'producto vendido sin costo' : 'productos vendidos sin costo' }}
                                    @else
                                        Todos los productos vendidos tienen costo
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Ventas y ganancia por mes --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="card-title-row mb-3">
                <h2>Ventas y ganancia · últimos 12 meses</h2>
                <span class="small text-body-secondary d-none d-sm-inline">Frente al mismo mes del año anterior</span>
            </div>
            <div class="chart-box chart-box-lg">
                <canvas data-report-chart="monthly" data-chart-data='@json($charts['monthly'])'
                    role="img" aria-label="Gráfico de ventas y ganancia por mes de los últimos 12 meses, comparado con el año anterior"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title-row mb-3"><h2>Ventas por categoría</h2></div>
                    @if ($hasSales)
                        <div class="chart-box">
                            <canvas data-report-chart="categories" data-chart-data='@json($charts['categories'])'
                                role="img" aria-label="Gráfico de dona de ventas por categoría en {{ $period->label() }}"></canvas>
                        </div>
                    @else
                        <x-empty-state icon="pie-chart" title="Sin ventas en el periodo" message="Cambia el periodo para ver las ventas por categoría." />
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title-row mb-3">
                        <h2>Ventas por marca</h2>
                        <span class="small text-body-secondary">Las {{ ReportService::BRAND_CHART_SIZE }} principales</span>
                    </div>
                    @if ($hasSales)
                        <div class="chart-box">
                            <canvas data-report-chart="brands" data-chart-data='@json($charts['brands'])'
                                role="img" aria-label="Gráfico de barras de ventas por marca en {{ $period->label() }}"></canvas>
                        </div>
                    @else
                        <x-empty-state icon="bar-chart" title="Sin ventas en el periodo" message="Cambia el periodo para ver las ventas por marca." />
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="card-title-row mb-3">
                <h2>Cotizaciones enviadas y aceptadas · últimos 12 meses</h2>
                <span class="small text-body-secondary d-none d-sm-inline">Por mes de envío</span>
            </div>
            <div class="chart-box">
                <canvas data-report-chart="quotes" data-chart-data='@json($charts['quotes'])'
                    role="img" aria-label="Gráfico de barras de cotizaciones enviadas y aceptadas por mes"></canvas>
            </div>
        </div>
    </div>

    {{-- Rankings --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-body pb-2">
                    <div class="card-title-row">
                        <h2>Más vendidos</h2>
                        <span class="small text-body-secondary">Por valor vendido</span>
                    </div>
                </div>
                @if ($tables['top_products']->isEmpty())
                    <x-empty-state icon="box-seam" title="Sin ventas en el periodo" />
                @else
                    <div class="table-responsive">
                        <table class="table table-dashboard align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Producto</th>
                                    <th scope="col" class="text-end">Cantidad</th>
                                    <th scope="col" class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tables['top_products'] as $row)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $row->name }}</div>
                                            <small class="text-body-secondary font-monospace">{{ $row->sku }}</small>
                                        </td>
                                        <td class="text-end text-nowrap">{{ $units($row->units) }} <small class="text-body-secondary">{{ $row->unit }}</small></td>
                                        <td class="text-end text-nowrap fw-semibold">{{ Money::cop($row->total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-body pb-2">
                    <div class="card-title-row">
                        <h2>Más cotizados</h2>
                        <span class="small text-body-secondary">Cotizaciones recibidas</span>
                    </div>
                </div>
                @if ($tables['top_quoted']->isEmpty())
                    <x-empty-state icon="file-earmark-text" title="Sin cotizaciones en el periodo" />
                @else
                    <div class="table-responsive">
                        <table class="table table-dashboard align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Producto</th>
                                    <th scope="col" class="text-end">Cotizaciones</th>
                                    <th scope="col" class="text-end">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tables['top_quoted'] as $row)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $row->name }}</div>
                                            <small class="text-body-secondary font-monospace">{{ $row->sku }}</small>
                                        </td>
                                        <td class="text-end">{{ $row->quotes }}</td>
                                        <td class="text-end text-nowrap">{{ $units($row->units) }} <small class="text-body-secondary">{{ $row->unit }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body pb-2">
            <div class="card-title-row">
                <h2>Mejores clientes</h2>
                <span class="small text-body-secondary">Por valor comprado</span>
            </div>
        </div>
        @if ($tables['top_customers']->isEmpty())
            <x-empty-state icon="people" title="Sin ventas en el periodo" />
        @else
            <div class="table-responsive">
                <table class="table table-dashboard align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Empresa</th>
                            <th scope="col" class="d-none d-sm-table-cell">NIT</th>
                            <th scope="col" class="text-end">Pedidos</th>
                            <th scope="col" class="text-end">Comprado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tables['top_customers'] as $row)
                            <tr>
                                <td class="fw-semibold">{{ $row->company }}</td>
                                <td class="d-none d-sm-table-cell font-monospace">{{ $row->nit }}</td>
                                <td class="text-end">{{ $row->orders }}</td>
                                <td class="text-end text-nowrap fw-semibold">{{ Money::cop($row->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
