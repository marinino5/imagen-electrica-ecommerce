@extends('layouts.admin')

@section('title', 'Cotización de '.$quote->name)

@section('breadcrumb')
    <li class="breadcrumb-item">General</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.quote-requests.index') }}">Cotizaciones sin cuenta</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $quote->name }}</li>
@endsection

@section('content')
    <div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1>{{ $quote->name }} @if ($quote->isFree())<span class="badge text-bg-warning align-middle fs-6">Cotización libre</span>@endif</h1>
            <p>{{ $quote->reference() }} · Recibida el {{ $quote->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }} · {{ $quote->status->label() }}</p>
        </div>
        <form method="POST" action="{{ route('admin.quote-requests.update', $quote) }}" class="d-flex gap-2">
            @csrf
            @method('PATCH')
            <label class="visually-hidden" for="status">Estado</label>
            <select name="status" id="status" class="form-select">
                @foreach (\App\Enums\QuoteRequestStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($quote->status === $case)>{{ $case->label() }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary">Guardar</button>
        </form>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-body-secondary">Contacto</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Teléfono</dt>
                        <dd class="col-sm-8"><a href="https://wa.me/{{ preg_replace('/\D/', '', $quote->phone) }}" target="_blank" rel="noopener">{{ $quote->phone }}</a></dd>
                        @if ($quote->email)
                            <dt class="col-sm-4">Correo</dt>
                            <dd class="col-sm-8"><a href="mailto:{{ $quote->email }}">{{ $quote->email }}</a></dd>
                        @endif
                        <dt class="col-sm-4">Ciudad</dt>
                        <dd class="col-sm-8">{{ $quote->city }}</dd>
                        @if ($quote->address)
                            <dt class="col-sm-4">Dirección</dt>
                            <dd class="col-sm-8">{{ $quote->address }}</dd>
                        @endif
                        @if ($quote->company)
                            <dt class="col-sm-4">Empresa</dt>
                            <dd class="col-sm-8">{{ $quote->company }}@if ($quote->nit) · NIT {{ $quote->nit }}@endif</dd>
                        @endif
                        @if ($quote->message && ! $quote->isFree())
                            <dt class="col-sm-4">Mensaje</dt>
                            <dd class="col-sm-8">{{ $quote->message }}</dd>
                        @endif
                        <dt class="col-sm-4">Política de datos</dt>
                        <dd class="col-sm-8">Aceptada el {{ $quote->privacy_accepted_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-body-secondary">Lo que pidió</h2>
                    @if ($quote->isFree())
                        <p class="mb-3" style="white-space: pre-line">{{ $quote->message }}</p>
                        @if ($quote->application)<p class="mb-3"><strong>Aplicación:</strong> {{ $quote->application }}</p>@endif
                        @if ($quote->attachment_name)
                            <a class="btn btn-outline-primary" href="{{ route('admin.quote-requests.attachment', $quote) }}">
                                <i class="bi bi-download" aria-hidden="true"></i> Descargar adjunto: {{ $quote->attachment_name }}
                            </a>
                        @else
                            <p class="text-body-secondary mb-0">Sin adjunto.</p>
                        @endif
                    @else
                    <div class="table-responsive">
                        <table class="table table-dashboard align-middle mb-0">
                            <thead><tr><th scope="col">Producto</th><th scope="col">SKU</th><th scope="col" class="text-end">Cantidad</th></tr></thead>
                            <tbody>
                                @foreach ($quote->items as $item)
                                    <tr>
                                        <td>{{ $item['name'] }}@if (! empty($item['label']))<small class="d-block text-body-secondary">{{ $item['label'] }}</small>@endif</td>
                                        <td>{{ $item['sku'] ?? 'Bajo pedido' }}</td>
                                        <td class="text-end">{{ rtrim(rtrim(number_format($item['quantity'], 2, ',', '.'), '0'), ',') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
