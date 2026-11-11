@extends('layouts.admin')

@section('title', 'Clientes')

@section('breadcrumb')
    <li class="breadcrumb-item">Clientes</li>
    <li class="breadcrumb-item active" aria-current="page">Clientes</li>
@endsection

@use('App\Support\Money')

@section('content')
    <div class="page-header">
        <h1>Clientes</h1>
        <p>Empresas y personas con cuenta en la tienda, con su historial de pedidos y cotizaciones.</p>
    </div>

    @include('admin.partials.tabs', ['group' => 'clientes'])

    <div class="card">
        <div class="card-body border-bottom">
            <x-search-bar :action="route('admin.customers.index')" :value="$search" placeholder="Buscar por empresa, contacto, NIT, correo o ciudad…" />
        </div>

        @if ($customers->isEmpty())
            <x-empty-state icon="people" :title="$search ? 'Sin resultados' : 'Aún no hay clientes'"
                :message="$search ? 'Ningún cliente coincide con la búsqueda.' : 'Cuando alguien cree su cuenta en la tienda, aparecerá aquí.'" />
        @else
            <div class="table-responsive">
                <table class="table table-dashboard align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Cliente</th>
                            <th scope="col" class="d-none d-lg-table-cell">Contacto</th>
                            <th scope="col" class="text-end">Pedidos</th>
                            <th scope="col" class="text-end">Cotizaciones</th>
                            <th scope="col" class="text-end d-none d-md-table-cell">Comprado</th>
                            <th scope="col" class="d-none d-xl-table-cell">Cliente desde</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $c)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.customers.show', $c) }}" class="fw-semibold text-decoration-none">{{ $c->company_name ?: $c->contact_name }}</a>
                                    <div class="text-body-secondary small">{{ $c->nit ? 'NIT '.$c->nit.' · ' : '' }}{{ $c->city }}</div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <div>{{ $c->contact_name }}</div>
                                    <div class="text-body-secondary small">{{ $c->user?->email }} · {{ $c->phone }}</div>
                                </td>
                                <td class="text-end">{{ $c->purchases_count }}</td>
                                <td class="text-end">{{ $c->quotes_count }}</td>
                                <td class="text-end d-none d-md-table-cell">{{ Money::cop($c->spent ?: null, '—') }}</td>
                                <td class="d-none d-xl-table-cell text-nowrap">{{ $c->created_at->timezone(config('app.timezone'))->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($customers->hasPages())
                <div class="card-footer">{{ $customers->links() }}</div>
            @endif
        @endif
    </div>
@endsection
