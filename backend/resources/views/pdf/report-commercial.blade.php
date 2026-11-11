@php
    $period = $report['period'];
    $q = $report['quotes'];
    $logo = public_path('images/logo-pdf.png');
    $logoData = is_file($logo) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logo)) : null;
    $units = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $rankings = [['Líneas más cotizadas', 'quoted_lines', 'Cotizaciones'], ['Familias más cotizadas', 'quoted_families', 'Cotizaciones'], ['Líneas más vendidas', 'sold_lines', 'Pedidos'], ['Familias más vendidas', 'sold_families', 'Pedidos']];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Análisis comercial</title>
    <style>
        @page { margin: 28px 34px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1C1630; margin: 0; }
        table { border-collapse: collapse; width: 100%; }
        h1 { font-size: 18px; margin: 0; }
        h2 { font-size: 11px; margin: 16px 0 5px; color: #1E9E4A; text-transform: uppercase; letter-spacing: .5px; }
        .rule { height: 4px; background: #1E9E4A; margin: 8px 0 12px; }
        .tag { display: inline-block; padding: 2px 8px; background: #e2f4e8; color: #167537; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        .muted { color: #6e6178; }
        th, td { border: 1px solid #d6dbe4; padding: 5px 7px; text-align: left; }
        th { background: #f0f8f2; }
        .r { text-align: right; }
        .foot { margin-top: 16px; font-size: 8.5px; color: #6e6178; border-top: 1px solid #d6dbe4; padding-top: 6px; }
    </style>
</head>
<body>
    <table style="border: 0"><tr>
        <td style="border: 0; padding: 0">@if ($logoData)<img src="{{ $logoData }}" style="height: 38px" alt="Imagen Eléctrica">@endif</td>
        <td style="border: 0; padding: 0; text-align: right"><span class="tag">Análisis comercial</span></td>
    </tr></table>
    <div class="rule"></div>
    <h1>Qué se cotiza, se vende y se consulta</h1>
    <p class="muted">Período: <strong>{{ $period->label() }}</strong> ({{ $period->start->format('d/m/Y') }} al {{ $period->lastDay()->format('d/m/Y') }})</p>

    <h2>Resumen</h2>
    <table>
        <tr><th>Indicador</th><th class="r">Valor</th></tr>
        <tr><td>Cotizaciones recibidas</td><td class="r">{{ $q['received'] }} ({{ $q['account'] }} con cuenta · {{ $q['free'] }} sin cuenta)</td></tr>
        <tr><td>Conversión cotización → pedido</td><td class="r">{{ $q['conversion'] === null ? 'Sin datos' : number_format($q['conversion'], 1, ',', '.').' %' }} ({{ $q['accepted'] }} aceptadas)</td></tr>
        <tr><td>Fichas descargadas</td><td class="r">{{ $report['datasheets']['total'] }}</td></tr>
        <tr><td>Suscriptores nuevos</td><td class="r">{{ $report['subscribers']->new }} ({{ $report['subscribers']->active }} activos · {{ $report['subscribers']->unsubscribed }} bajas)</td></tr>
    </table>

    @foreach ($rankings as [$title, $key, $count])
        <h2>{{ $title }}</h2>
        <table>
            <tr><th>Nombre</th><th class="r">{{ $count }}</th><th class="r">Unidades</th></tr>
            @forelse ($report[$key] as $row)
                <tr><td>{{ $row->name }}</td><td class="r">{{ $row->requests }}</td><td class="r">{{ $units($row->units) }}</td></tr>
            @empty
                <tr><td colspan="3" class="muted">Sin datos en el período.</td></tr>
            @endforelse
        </table>
    @endforeach

    <p class="foot">Lo «más consultado» se mide con las fichas descargadas. Cotizado = cotizaciones con cuenta del período; vendido = pedidos pagados, enviados y entregados. Generado el {{ now()->timezone(config('app.timezone'))->format('d/m/Y H:i') }}.</p>
</body>
</html>
