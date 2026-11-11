<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Recibimos tu solicitud</title>
</head>
<body style="margin:0;padding:0;background:#f3f1fa;font-family:Arial,Helvetica,sans-serif;color:#1C1630;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f1fa;padding:32px 12px;">
  <tr><td align="center">
    <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;">
      <tr><td style="background:#4A3580;padding:24px 32px;">
        <span style="font-size:24px;font-weight:700;color:#ffffff;letter-spacing:.5px;">Imagen <span style="color:#ffb27a;">Eléctrica</span></span><br>
        <span style="font-size:13px;color:#d6d0ea;">Soluciones Industriales</span>
      </td></tr>
      <tr><td style="height:4px;background:#f26a00;font-size:0;line-height:0;">&nbsp;</td></tr>
      <tr><td style="padding:32px;">
        @php($first = $orders->first())
        <h1 style="margin:0 0 12px;font-size:22px;line-height:1.2;color:#1C1630;">¡Gracias por tu solicitud, {{ strtok($first->contact_name, ' ') }}!</h1>
        <p style="margin:0 0 20px;font-size:15px;line-height:1.6;">Recibimos {{ $orders->count() > 1 ? 'tus solicitudes' : 'tu solicitud' }}. Un asesor te contactará para confirmar el pago y la entrega.</p>

        @foreach ($orders as $order)
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 16px;border:1px solid #e6e2f3;border-radius:8px;">
            <tr><td style="padding:14px 16px;border-bottom:1px solid #e6e2f3;">
              <strong style="font-size:16px;">{{ $order->code }}</strong> · {{ $order->type->label() }}
              <span style="float:right;color:#5d5672;font-size:13px;">{{ $order->status->label() }}</span>
            </td></tr>
            @foreach ($order->items as $item)
              <tr><td style="padding:8px 16px;font-size:14px;">
                {{ rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }} × {{ $item->product_name }}
                <span style="float:right;">{{ $item->line_total === null ? 'Por cotizar' : '$ '.number_format((float) $item->line_total, 0, ',', '.') }}</span>
              </td></tr>
            @endforeach
            <tr><td style="padding:12px 16px;border-top:1px solid #e6e2f3;font-size:14px;">
              Envío: {{ $order->shipping === null ? 'Lo confirma tu asesor' : ((float) $order->shipping === 0.0 ? 'Gratis' : '$ '.number_format((float) $order->shipping, 0, ',', '.')) }}<br>
              <strong style="font-size:16px;">Total: {{ $order->total === null ? 'Por valorar' : '$ '.number_format((float) $order->total, 0, ',', '.') }}</strong> <span style="color:#5d5672;">(IVA incluido)</span>
            </td></tr>
            <tr><td style="padding:0 16px 16px;">
              <a href="{{ $links[$order->code] }}" style="display:inline-block;background:#f26a00;color:#1C1630;font-weight:700;font-size:14px;text-decoration:none;padding:12px 24px;border-radius:999px;">Ver el estado de {{ $order->code }}</a>
            </td></tr>
          </table>
        @endforeach

        @if ($first->needs_invoice)
          <p style="margin:16px 0 8px;font-size:14px;line-height:1.6;"><strong>Facturación a nombre de:</strong> {{ $first->company_name ?: trim($first->billing_first_name.' '.$first->billing_last_name) }} · {{ $first->billing_id_type?->short() }} {{ $first->billing_identification }}{{ $first->billing_check_digit !== null ? '-'.$first->billing_check_digit : '' }}</p>
        @endif
        <p style="margin:0 0 12px;font-size:12px;line-height:1.6;color:#5d5672;">Guarda este correo: el enlace es personal y vale {{ config('shop.guest_link_days') }} días. Si no hiciste esta solicitud, responde este mensaje.</p>
      </td></tr>
      <tr><td style="background:#faf9fd;padding:16px 32px;font-size:12px;color:#5d5672;">Imagen Eléctrica · Cra 18 No 24-32, Bucaramanga, Santander</td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
