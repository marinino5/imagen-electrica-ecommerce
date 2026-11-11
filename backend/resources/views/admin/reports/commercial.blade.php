@extends('layouts.admin')

@section('title', 'Análisis comercial')

@section('breadcrumb')
    <li class="breadcrumb-item">Análisis</li>
    <li class="breadcrumb-item active" aria-current="page">Comercial</li>
@endsection

@use('App\Enums\ReportPreset')

@php
    $period = $report['period'];
    $q = $report['quotes'];
    $subs = $report['subscribers'];
    $units = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $kpis = [
        ['Cotizaciones recibidas', $q['received'], "{$q['account']} con cuenta · {$q['free']} sin cuenta", 'chat-left-text'],
        ['Conversión cotización → pedido', $q['conversion'] === null ? 'Sin datos' : number_format($q['conversion'], 1, ',', '.').' %', "{$q['accepted']} aceptadas de {$q['account']} con cuenta", 'arrow-left-right'],
        ['Fichas descargadas', $report['datasheets']['total'], 'PDF con cotización desde la tienda', 'file-earmark-pdf'],
        ['Suscriptores nuevos', $subs->new, "{$subs->active} activos · {$subs->unsubscribed} bajas en el periodo", 'envelope-paper'],
    ];
    $rankings = [
        ['Líneas más cotizadas', $report['quoted_lines'], 'cotizaciones', true],
        ['Familias más cotizadas', $report['quoted_families'], 'cotizaciones', false],
        ['Líneas más vendidas', $report['sold_lines'], 'pedidos', true],
        ['Familias más vendidas', $report['sold_families'], 'pedidos', false],
    ];
@endphp

@section('content')
    <div class="page-header animate-in">
        <h1>Análisis</h1>
        <p>Lo que se cotiza, se vende y se consulta en la tienda durante <strong>{{ $period->label() }}</strong>.</p>
    </div>

    @include('admin.partials.tabs', ['group' => 'analisis'])

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.commercial') }}" class="row g-2 align-items-end" data-report-filter>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <label for="period" class="form-label small mb-1">Periodo</label>
                    <select id="period" name="period" class="form-select" data-report-period>
                        @foreach ($presets as $option)
                            <option value="{{ $option->value }}" @selected($preset === $option)>{{ $option->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2" data-report-custom @if ($preset !== ReportPreset::Custom) hidden @endif>
                    <label for="from" class="form-label small mb-1">Desde</label>
                    <input type="date" id="from" name="from" value="{{ old('from', $period->start->toDateString()) }}" class="form-control">
                </div>
                <div class="col-6 col-md-3 col-xl-2" data-report-custom @if ($preset !== ReportPreset::Custom) hidden @endif>
                    <label for="to" class="form-label small mb-1">Hasta</label>
                    <input type="date" id="to" name="to" value="{{ old('to', $period->lastDay()->toDateString()) }}" class="form-control">
                </div>
                <div class="col-auto"><button type="submit" class="btn btn-primary">Aplicar</button></div>
            </form>
            <p class="small text-body-secondary mt-3 mb-0">
                Lo «más consultado» se mide con las fichas descargadas (la tienda no registra visitas a cada ficha). Cotizado = cotizaciones con cuenta
                creadas en el periodo; vendido = pedidos pagados, enviados y entregados.
            </p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach ($kpis as $i => [$label, $value, $note, $icon])
            <div class="col-12 col-sm-6 col-xl-3 animate-in" style="--delay: {{ $i * 60 }}ms">
                <div class="card stat-card h-100"><div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-value">{{ is_numeric($value) ? number_format($value, 0, ',', '.') : $value }}</div>
                        <span class="stat-icon" aria-hidden="true"><i class="bi bi-{{ $icon }}"></i></span>
                    </div>
                    <div class="stat-label">{{ $label }}</div>
                    <div class="small text-body-secondary mt-1">{{ $note }}</div>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        @foreach ($rankings as [$title, $rows, $unit, $withFamily])
            <div class="col-12 col-xl-6">
                <div class="card h-100">
                    <div class="card-body border-bottom"><h2 class="h5 mb-0">{{ $title }}</h2></div>
                    @if ($rows->isEmpty())
                        <x-empty-state icon="bar-chart" title="Sin datos en este periodo" message="Cuando haya {{ $unit }} en estas fechas, el ranking aparece aquí." />
                    @else
                        <div class="table-responsive">
                            <table class="table table-dashboard align-middle mb-0">
                                <thead><tr><th>Nombre</th><th class="text-end">{{ ucfirst($unit) }}</th><th class="text-end">Unidades</th></tr></thead>
                                <tbody>
                                    @foreach ($rows as $row)
                                        <tr>
                                            <td><span class="fw-semibold">{{ $row->name }}</span>@if ($withFamily && $row->family)<div class="small text-body-secondary">{{ $row->family }}</div>@endif</td>
                                            <td class="text-end">{{ $row->requests }}</td>
                                            <td class="text-end">{{ $units($row->units) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="col-12">
            <div class="card">
                <div class="card-body border-bottom"><h2 class="h5 mb-0">Productos más consultados (fichas descargadas)</h2></div>
                @if ($report['datasheets']['top']->isEmpty())
                    <x-empty-state icon="file-earmark-pdf" title="Aún no hay fichas descargadas" message="Cada «Descargar ficha y cotización» de la tienda suma aquí." />
                @else
                    <div class="table-responsive">
                        <table class="table table-dashboard align-middle mb-0">
                            <thead><tr><th>Línea o producto</th><th class="text-end">Descargas</th></tr></thead>
                            <tbody>
                                @foreach ($report['datasheets']['top'] as $row)
                                    <tr><td class="fw-semibold">{{ $row->name }}</td><td class="text-end">{{ $row->downloads }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
