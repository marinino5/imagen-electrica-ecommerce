@extends('layouts.admin')

@section('title', 'Pedidos')

@section('breadcrumb')
    <li class="breadcrumb-item">Ventas</li>
    <li class="breadcrumb-item active" aria-current="page">Pedidos</li>
@endsection

@use('App\Support\Money')
@inject('orderService', 'App\Services\OrderService')

@php($statusValue = implode(',', $filters['status']))

@section('content')
    <div class="page-header">
        <h1>Ventas</h1>
        <p>Pedidos, cotizaciones y fichas descargadas desde la tienda.</p>
    </div>

    @include('admin.partials.tabs', ['group' => 'ventas'])

    <div class="card">
        <div class="card-body border-bottom">
            <x-search-bar :action="route('admin.orders.index')" :value="$filters['q']" placeholder="Buscar por código, empresa, NIT o contacto…">
                <input type="hidden" name="type" value="purchase">
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="status" class="visually-hidden">Estado</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Todos los estados</option>
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
            <x-empty-state icon="inboxes" :title="request()->query() ? 'Sin resultados' : 'Aún no hay pedidos'"
                :message="request()->query() ? 'Ningún pedido cumple esos filtros. Prueba con otros.' : 'Los pedidos de la tienda aparecerán aquí.'" />
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
                                    <div class="fw-semibold">{{ $order->display_name }}
                                        @if ($order->is_guest && $order->customer_id === null)<span class="badge text-bg-warning ms-1">Invitado</span>@endif
                                    </div>
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
