@extends('layouts.admin')

@section('title', $customer->company_name ?: $customer->contact_name)

@section('breadcrumb')
    <li class="breadcrumb-item">Clientes</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Clientes</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $customer->company_name ?: $customer->contact_name }}</li>
@endsection

@use('App\Support\Money')

@section('content')
    <div class="page-header">
        <h1>{{ $customer->company_name ?: $customer->contact_name }}</h1>
        <p>{{ $customer->type->label() }} · cliente desde {{ $customer->created_at->timezone(config('app.timezone'))->format('d/m/Y') }}</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-5">
            <div class="card h-100"><div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Contacto</dt><dd class="col-sm-8">{{ $customer->contact_name }}</dd>
                    <dt class="col-sm-4">Correo</dt><dd class="col-sm-8">{{ $customer->user?->email }}</dd>
                    <dt class="col-sm-4">Teléfono</dt><dd class="col-sm-8">{{ $customer->phone }}</dd>
                    @if ($customer->nit)<dt class="col-sm-4">NIT</dt><dd class="col-sm-8">{{ $customer->nit }}</dd>@endif
                    <dt class="col-sm-4">Ciudad</dt><dd class="col-sm-8">{{ $customer->city }}@if ($customer->address) · {{ $customer->address }}@endif</dd>
                    <dt class="col-sm-4">Boletín</dt><dd class="col-sm-8">{{ $customer->marketing_opt_in ? 'Aceptó recibir novedades' : 'No aceptó' }}</dd>
                </dl>
            </div></div>
        </div>
        <div class="col-12 col-lg-7">
            <div class="row g-3 h-100">
                @foreach ([['Pedidos', $purchases->count()], ['Cotizaciones', $quotes->count() + $free->count()], ['Comprado', Money::cop($spent ?: null, '$ 0')]] as [$label, $value])
                    <div class="col-4"><div class="card h-100"><div class="card-body">
                        <div class="stat-value fs-2">{{ $value }}</div>
                        <div class="stat-label">{{ $label }}</div>
                    </div></div></div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body border-bottom"><h2 class="h5 mb-0">Pedidos y cotizaciones con cuenta</h2></div>
        @if ($customer->orders->isEmpty())
            <x-empty-state icon="inboxes" title="Sin solicitudes" message="Este cliente todavía no ha enviado pedidos ni cotizaciones." />
        @else
            <div class="table-responsive">
                <table class="table table-dashboard align-middle">
                    <thead><tr><th>Código</th><th>Tipo</th><th>Estado</th><th class="text-end">Total</th><th>Fecha</th></tr></thead>
                    <tbody>
                        @foreach ($customer->orders as $o)
                            <tr>
                                <td class="font-monospace"><a href="{{ route('admin.orders.show', $o) }}" class="fw-semibold text-decoration-none">{{ $o->code }}</a></td>
                                <td>{{ $o->type->label() }}</td>
                                <td><x-status-badge :status="$o->status" /></td>
                                <td class="text-end">{{ Money::cop($o->total, 'Por definir') }}</td>
                                <td class="text-nowrap">{{ $o->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($free->isNotEmpty())
        <div class="card">
            <div class="card-body border-bottom"><h2 class="h5 mb-0">Cotizaciones enviadas sin cuenta con este correo</h2></div>
            <div class="table-responsive">
                <table class="table table-dashboard align-middle">
                    <thead><tr><th>Referencia</th><th>Estado</th><th>Fecha</th></tr></thead>
                    <tbody>
                        @foreach ($free as $f)
                            <tr>
                                <td class="font-monospace"><a href="{{ route('admin.quote-requests.show', $f) }}" class="fw-semibold text-decoration-none">{{ $f->reference() }}</a></td>
                                <td>{{ $f->status->label() }}</td>
                                <td class="text-nowrap">{{ $f->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
