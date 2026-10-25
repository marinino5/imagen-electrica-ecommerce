@extends('layouts.admin')

@section('title', 'Solicitudes')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Solicitudes</li>
@endsection

@use('App\Support\Money')
@inject('orderService', 'App\Services\OrderService')

@php($statusValue = implode(',', $filters['status']))

@section('content')
    <div class="page-header">
        <h1>Solicitudes</h1>
        <p>Pedidos y cotizaciones de los clientes.</p>
    </div>

    <div class="card">
        <div class="card-body border-bottom">
            <x-search-bar :action="route('admin.orders.index')" :value="$filters['q']" placeholder="Buscar por código, empresa, NIT o contacto…">
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="type" class="visually-hidden">Tipo</label>
                    <select id="type" name="type" class="form-select">
                        <option value="">Pedidos y cotizaciones</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}" @selected($filters['type'] === $type->value)>{{ $type->label() }}s</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="status" class="visually-hidden">Estado</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="new,in_review" @selected($statusValue === 'new,in_review')>Cotizaciones sin responder</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($statusValue === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <div class="form-check form-switch mb-0 pt-1">
                        <input class="form-check-input" type="checkbox" role="switch" id="overdue" name="overdue" value="1" @checked($filters['overdue'])>
                        <label class="form-check-label" for="overdue">Solo atrasadas (+{{ $overdueHours }} h)</label>
                    </div>
                </div>
            </x-search-bar>
        </div>

        @if ($orders->isEmpty())
            <x-empty-state icon="inboxes" :title="request()->query() ? 'Sin resultados' : 'Aún no hay solicitudes'"
                :message="request()->query() ? 'Ninguna solicitud cumple esos filtros. Prueba con otros.' : 'Los pedidos y cotizaciones de la tienda aparecerán aquí.'" />
        @else
            <div class="table-responsive">
                <table class="table table-dashboard align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Código</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end d-none d-md-table-cell">Total</th>
                            <th scope="col" class="d-none d-lg-table-cell">Responsable</th>
                            <th scope="col" class="d-none d-sm-table-cell">Recibida</th>
                            <th scope="col" class="text-end"><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="order-code text-decoration-none">{{ $order->code }}</a>
                                    <div><small class="text-body-secondary">{{ $order->type->label() }}</small></div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $order->display_name }}</div>
                                    <small class="text-body-secondary">{{ $order->customer_type->requiresCompanyData() ? $order->contact_name : $order->customer_type->label() }}</small>
                                </td>
                                <td>
                                    <x-status-badge :status="$order->status" />
                                    @if ($orderService->isOverdue($order))
                                        <span class="hours-badge is-overdue ms-1"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i> Atrasada</span>
                                    @endif
                                </td>
                                <td class="text-end d-none d-md-table-cell">{{ Money::cop($order->total, 'Por definir') }}</td>
                                <td class="d-none d-lg-table-cell">{{ $order->assignee?->name ?? '—' }}</td>
                                <td class="d-none d-sm-table-cell text-nowrap">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                    <div><small class="text-body-secondary">{{ $order->created_at->locale('es')->diffForHumans() }}</small></div>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                        Ver<span class="visually-hidden"> {{ $order->code }}</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="card-body border-top">{{ $orders->links() }}</div>
            @endif
        @endif
    </div>
@endsection
