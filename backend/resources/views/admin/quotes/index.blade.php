@extends('layouts.admin')

@section('title', 'Cotizaciones')

@section('breadcrumb')
    <li class="breadcrumb-item">Ventas</li>
    <li class="breadcrumb-item active" aria-current="page">Cotizaciones</li>
@endsection

@use('App\Support\Money')

@section('content')
    <div class="page-header">
        <h1>Ventas</h1>
        <p>Pedidos, cotizaciones y fichas descargadas desde la tienda.</p>
    </div>

    @include('admin.partials.tabs', ['group' => 'ventas'])

    <div class="card" data-skeleton-target>
        <div class="card-body border-bottom">
            <x-filter-chips param="state" label="Filtrar por estado" :options="['' => 'Todas', 'pending' => 'Sin responder', 'answered' => 'Respondidas', 'cancelled' => 'Canceladas']" />
            <x-filter-chips param="origin" label="Filtrar por origen" :options="['' => 'Con y sin cuenta', 'account' => 'Con cuenta', 'free' => 'Sin cuenta']" />
            <x-search-bar :action="route('admin.quotes.index')" :value="$filters['q']" placeholder="Buscar por código, empresa, contacto o correo…">
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="origin" class="visually-hidden">Origen</label>
                    <select id="origin" name="origin" class="form-select">
                        <option value="">Con y sin cuenta</option>
                        <option value="account" @selected($filters['origin'] === 'account')>Con cuenta</option>
                        <option value="free" @selected($filters['origin'] === 'free')>Sin cuenta</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="state" class="visually-hidden">Estado</label>
                    <select id="state" name="state" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pending" @selected($filters['state'] === 'pending')>Sin responder</option>
                        <option value="answered" @selected($filters['state'] === 'answered')>Respondidas</option>
                        <option value="cancelled" @selected($filters['state'] === 'cancelled')>Canceladas</option>
                    </select>
                </div>
            </x-search-bar>
        </div>

        @if ($quotes->isEmpty())
            <x-empty-state icon="chat-left-text" :title="request()->query() ? 'Sin resultados' : 'Aún no hay cotizaciones'"
                :message="request()->query() ? 'Ninguna cotización cumple esos filtros. Prueba con otros.' : 'Las cotizaciones de la tienda, con o sin cuenta, aparecerán aquí.'" />
        @else
            <div class="table-responsive">
                <table class="table table-dashboard align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Código</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Origen</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end d-none d-md-table-cell">Valor</th>
                            <th scope="col" class="d-none d-lg-table-cell">Recibida</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quotes as $q)
                            <tr>
                                <td class="font-monospace text-nowrap"><a href="{{ $q->url }}" class="fw-semibold text-decoration-none">{{ $q->code }}</a></td>
                                <td>
                                    <div class="fw-semibold">{{ $q->client }}</div>
                                    <div class="text-body-secondary small">{{ $q->contact_name }}@if ($q->city) · {{ $q->city }}@endif</div>
                                </td>
                                <td><span @class(['badge', 'text-bg-primary' => $q->source === 'order', 'text-bg-secondary' => $q->source === 'free'])>{{ $q->origin_label }}</span></td>
                                <td>
                                    <span class="status-badge {{ $q->pending ? 'status-new' : 'status-sent' }}">{{ $q->status_label }}</span>
                                    @if ($q->overdue)<span class="badge text-bg-danger ms-1" title="Más de {{ $overdueHours }} h sin respuesta">Atrasada</span>@endif
                                </td>
                                <td class="text-end d-none d-md-table-cell">{{ Money::cop($q->total, 'Por definir') }}</td>
                                <td class="d-none d-lg-table-cell text-nowrap">{{ $q->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($quotes->hasPages())
                <div class="card-footer">{{ $quotes->links() }}</div>
            @endif
        @endif
    </div>
@endsection
