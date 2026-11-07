Nueva cotización libre {{ $quote->reference() }}

Nombre: {{ $quote->name }}
Teléfono: {{ $quote->phone }}
@if ($quote->email)Correo: {{ $quote->email }}
@endif
Dirección: {{ $quote->address }}, {{ $quote->city }}
@if ($quote->company)Empresa: {{ $quote->company }}@if ($quote->nit) (NIT {{ $quote->nit }})@endif

@endif

Qué necesita:
{{ $quote->message }}
@if ($quote->application)

Aplicación: {{ $quote->application }}
@endif
@if ($quote->attachment_name)

Adjunto: {{ $quote->attachment_name }} (va en este correo y se descarga desde el panel)
@endif

Panel: {{ route('admin.quote-requests.show', $quote) }}
