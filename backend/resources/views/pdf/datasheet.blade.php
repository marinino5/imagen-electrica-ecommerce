@php
    use App\Enums\DatasheetPriceKind;
    use App\Support\Money;
    $logo = public_path('images/logo-pdf.png');
    $logoData = is_file($logo) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logo)) : null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha técnica {{ $number }}</title>
    <style>
        @page { margin: 28px 34px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #261c3e; margin: 0; }
        table { border-collapse: collapse; width: 100%; }
        .muted { color: #6e6178; }
        .head td { vertical-align: middle; }
        .brand { font-size: 17px; font-weight: bold; color: #513e84; text-transform: uppercase; }
        .doc { text-align: right; font-size: 9.5px; line-height: 1.6; }
        .doc strong { font-size: 12px; color: #513e84; }
        .rule { height: 3px; background: #513e84; margin: 8px 0 14px; }
        h1 { font-size: 19px; margin: 0 0 3px; color: #261c3e; }
        .ref { font-size: 11px; color: #6e6178; margin-bottom: 10px; }
        .photo { text-align: center; padding: 6px 0 10px; }
        .photo img { max-height: 190px; max-width: 260px; }
        h2 { font-size: 11px; margin: 14px 0 5px; color: #513e84; text-transform: uppercase; letter-spacing: .5px; }
        .spec th, .spec td { border: 1px solid #d6ccea; padding: 5px 8px; text-align: left; font-size: 10px; }
        .spec th { width: 34%; background: #f6f4f9; font-weight: bold; }
        .val th, .val td { border: 1px solid #d6ccea; padding: 6px 8px; font-size: 10.5px; }
        .val th { background: #f6f4f9; text-align: left; }
        .val td.r, .val th.r { text-align: right; }
        .total td { font-weight: bold; font-size: 12px; background: #eeeaf7; }
        .note { margin-top: 8px; padding: 8px 10px; border: 1px solid #f0c9a6; background: #fff6ec; font-size: 10px; }
        .quote { margin-top: 4px; padding: 10px 12px; border: 1px solid #d6ccea; background: #f6f4f9; }
        .foot { margin-top: 18px; padding-top: 8px; border-top: 1px solid #d6ccea; font-size: 9px; color: #6e6178; line-height: 1.6; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td style="width: 60px;">@if ($logoData)<img src="{{ $logoData }}" style="height: 54px;">@endif</td>
            <td><span class="brand">Imagen Eléctrica</span><br><span class="muted">Soluciones Industriales · Bucaramanga</span></td>
            <td class="doc">
                <strong>{{ $number }}</strong><br>
                Ficha técnica y cotización<br>
                Fecha: {{ $date->format('d/m/Y') }}<br>
                Válida por {{ $days }} días (hasta {{ $validUntil->format('d/m/Y') }})
            </td>
        </tr>
    </table>
    <div class="rule"></div>

    <h1>{{ $title }}</h1>
    <div class="ref">{{ $product->variant_label ? $product->variant_label.' · ' : '' }}SKU {{ $product->sku }}</div>
    @if ($image)
        <div class="photo"><img src="{{ $image }}"></div>
    @endif

    <h2>Especificaciones</h2>
    <table class="spec">
        @foreach ($rows as [$label, $value])
            <tr><th>{{ $label }}</th><td>{{ $value }}</td></tr>
        @endforeach
    </table>

    <h2>Valor</h2>
    @if ($kind === DatasheetPriceKind::Quote)
        <div class="quote">
            <strong>Precio sujeto a cotización.</strong><br>
            Cantidad solicitada: {{ rtrim(rtrim(number_format($quantity, 2, ',', '.'), '0'), ',') }}.<br>
            Pídela aquí: {{ $quoteUrl }}
        </div>
    @else
        <table class="val">
            <tr><th>Cantidad</th><th class="r">Precio unitario (IVA incl.)</th><th class="r">Subtotal</th><th class="r">IVA {{ rtrim(rtrim(number_format($prices['rate'], 2, ',', ''), '0'), ',') }}%</th></tr>
            <tr>
                <td>{{ rtrim(rtrim(number_format($quantity, 2, ',', '.'), '0'), ',') }}</td>
                <td class="r">{{ Money::cop($prices['unit']) }}</td>
                <td class="r">{{ Money::cop($prices['subtotal']) }}</td>
                <td class="r">{{ Money::cop($prices['tax']) }}</td>
            </tr>
            <tr class="total"><td colspan="3" class="r">Total (COP)</td><td class="r">{{ Money::cop($prices['total']) }}</td></tr>
        </table>
        @if ($kind === DatasheetPriceKind::Reference)
            <div class="note"><strong>Precio de referencia sujeto a confirmación por un asesor.</strong></div>
        @endif
    @endif

    <div class="foot">
        <strong>Imagen Eléctrica</strong> · {{ $site['address'] }}<br>
        WhatsApp y teléfono {{ $site['phone'] }} · {{ $site['email'] }}<br>
        Documento informativo; la venta se confirma con un asesor. Disponibilidad y tiempo de entrega sujetos a confirmación.
    </div>
</body>
</html>
