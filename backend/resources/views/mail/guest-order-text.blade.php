¡Gracias por tu solicitud!

@foreach ($orders as $order)
{{ $order->code }} · {{ $order->type->label() }} · {{ $order->status->label() }}
@foreach ($order->items as $item)
  - {{ rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }} x {{ $item->product_name }}
@endforeach
Total: {{ $order->total === null ? 'por valorar' : '$ '.number_format((float) $order->total, 0, ',', '.') }} (IVA incluido)
Estado: {{ $links[$order->code] }}

@endforeach
Un asesor te contactará para confirmar el pago y la entrega. El enlace es personal y vale {{ config('shop.guest_link_days') }} días.

Imagen Eléctrica · Cra 18 No 24-32, Bucaramanga
