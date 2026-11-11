@php
    use App\Support\Money;
    $period = $report['period'];
    $logo = public_path('images/logo-pdf.png');
    $logoData = is_file($logo) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logo)) : null;
    $decimal = fn ($v) => number_format((float) $v, 1, ',', '.');
    $value = fn (array $i, $v) => match (true) {
        $v === null => 'Sin datos',
        $i['format'] === 'money' => Money::cop($v),
        $i['format'] === 'percent' => $decimal($v).' %',
        $i['format'] === 'hours' => $decimal($v).' h',
        default => number_format((float) $v, 0, ',', '.'),
    };
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Análisis financiero</title>
    <style>
        @page { margin: 28px 34px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1C1630; margin: 0; }
        table { border-collapse: collapse; width: 100%; }
        h1 { font-size: 18px; margin: 0; color: #1C1630; }
        h2 { font-size: 11px; margin: 16px 0 5px; color: #005BAC; text-transform: uppercase; letter-spacing: .5px; }
        .rule { height: 4px; background: #005BAC; margin: 8px 0 12px; }
        .tag { display: inline-block; padding: 2px 8px; background: #e3eefa; color: #005BAC; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        .muted { color: #6e6178; }
        th, td { border: 1px solid #d6dbe4; padding: 5px 7px; text-align: left; }
        th { background: #f1f5fa; }
        .r { text-align: right; }
        .foot { margin-top: 16px; font-size: 8.5px; color: #6e6178; border-top: 1px solid #d6dbe4; padding-top: 6px; }
    </style>
</head>
<body>
    <table style="border: 0"><tr>
        <td style="border: 0; padding: 0">@if ($logoData)<img src="{{ $logoData }}" style="height: 38px" alt="Imagen Eléctrica">@endif</td>
        <td style="border: 0; padding: 0; text-align: right"><span class="tag">Análisis financiero</span></td>
    </tr></table>
    <div class="rule"></div>
    <h1>Ventas, ganancia y cotizaciones</h1>
    <p class="muted">Período: <strong>{{ $period->label() }}</strong> ({{ $period->start->format('d/m/Y') }} al {{ $period->lastDay()->format('d/m/Y') }}) · comparado con {{ $period->previousLabel() }}</p>

    <h2>Indicadores</h2>
    <table>
        <tr><th>Indicador</th><th class="r">Período</th><th class="r">Período anterior</th><th class="r">Cambio</th></tr>
        @foreach ($report['indicators'] as $i)
            <tr>
                <td>{{ $i['label'] }}</td>
                <td class="r">{{ $value($i, $i['value']) }}</td>
                <td class="r">{{ $value($i, $i['previous']) }}</td>
                <td class="r">{{ $i['change'] === null ? '—' : ($i['change'] > 0 ? '+' : '').$decimal($i['change']).($i['format'] === 'percent' ? ' pp' : ' %') }}</td>
            </tr>
        @endforeach
    </table>

    <h2>Productos más vendidos</h2>
    <table>
        <tr><th>Referencia</th><th>Producto</th><th class="r">Cantidad</th><th class="r">Vendido</th></tr>
        @forelse ($report['tables']['top_products'] as $row)
            <tr><td>{{ $row->sku }}</td><td>{{ $row->name }}</td><td class="r">{{ rtrim(rtrim(number_format($row->units, 2, ',', '.'), '0'), ',') }} {{ $row->unit }}</td><td class="r">{{ Money::cop($row->total) }}</td></tr>
        @empty
            <tr><td colspan="4" class="muted">Sin ventas en el período.</td></tr>
        @endforelse
    </table>

    <h2>Mejores clientes</h2>
    <table>
        <tr><th>Cliente</th><th>Documento</th><th class="r">Pedidos</th><th class="r">Comprado</th></tr>
        @forelse ($report['tables']['top_customers'] as $row)
            <tr><td>{{ $row->company }}</td><td>{{ $row->document['full'] ?? '—' }} @if ($row->document)<span class="muted">({{ $row->document['kind'] }})</span>@endif</td><td class="r">{{ $row->orders }}</td><td class="r">{{ Money::cop($row->total) }}</td></tr>
        @empty
            <tr><td colspan="4" class="muted">Sin ventas en el período.</td></tr>
        @endforelse
    </table>

    <p class="foot">Se cuentan los pedidos pagados, enviados y entregados, fechados al momento del pago. Los valores incluyen IVA; la ganancia y el margen se calculan sobre la base sin IVA frente al costo de cada producto. Generado el {{ now()->timezone(config('app.timezone'))->format('d/m/Y H:i') }}.</p>
</body>
</html>
