@extends('layouts.admin')

@section('title', $order->code)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Solicitudes</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $order->code }}</li>
@endsection

@use('App\Enums\OrderStatus')
@use('App\Support\Money')
@inject('orderService', 'App\Services\OrderService')

@php
    $isQuote = $order->type === \App\Enums\OrderType::Quote;
    $destructive = [OrderStatus::Cancelled, OrderStatus::Rejected];
@endphp

@section('content')
    <div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1>{{ $order->type->label() }} {{ $order->code }}</h1>
            <p>
                Recibida el {{ $order->created_at->format('d/m/Y H:i') }}
                @if ($order->quote)
                    · Generado desde la cotización <a href="{{ route('admin.orders.show', $order->quote) }}">{{ $order->quote->code }}</a>
                @endif
                @if ($order->generatedOrder)
                    · Pedido generado: <a href="{{ route('admin.orders.show', $order->generatedOrder) }}">{{ $order->generatedOrder->code }}</a>
                @endif
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if ($orderService->isOverdue($order))
                <span class="hours-badge is-overdue"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i> Atrasada</span>
            @endif
            <x-status-badge :status="$order->status" class="fs-6" />
        </div>
    </div>

    <div class="row g-3 mb-3">
        {{-- Cliente --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Cliente</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Empresa</dt>
                        <dd class="col-sm-8">{{ $order->company_name }} <small class="text-body-secondary">· NIT {{ $order->nit }}</small></dd>
                        <dt class="col-sm-4">Contacto</dt>
                        <dd class="col-sm-8">{{ $order->contact_name }}</dd>
                        <dt class="col-sm-4">Correo</dt>
                        <dd class="col-sm-8"><a href="mailto:{{ $order->email }}">{{ $order->email }}</a></dd>
                        <dt class="col-sm-4">Teléfono</dt>
                        <dd class="col-sm-8">{{ $order->phone }}</dd>
                        <dt class="col-sm-4">Ciudad</dt>
                        <dd class="col-sm-8">{{ $order->city }}{{ $order->address ? ' · '.$order->address : '' }}</dd>
                        <dt class="col-sm-4">Entrega</dt>
                        <dd class="col-sm-8">{{ $order->delivery_method->label() }}</dd>
                        @if ($order->notes)
                            <dt class="col-sm-4">Notas del cliente</dt>
                            <dd class="col-sm-8">{!! nl2br(e($order->notes)) !!}</dd>
                        @endif
                        @if ($order->paid_at)
                            <dt class="col-sm-4">Pagado</dt>
                            <dd class="col-sm-8">{{ $order->paid_at->format('d/m/Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            {{-- Responsable --}}
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="h5 mb-3">Responsable</h2>
                    <form method="POST" action="{{ route('admin.orders.assignment', $order) }}" class="d-flex gap-2">
                        @csrf
                        @method('PATCH')
                        <label for="assigned_to" class="visually-hidden">Comercial responsable</label>
                        <select id="assigned_to" name="assigned_to" @class(['form-select', 'is-invalid' => $errors->has('assigned_to')])>
                            <option value="">Sin asignar</option>
                            @foreach ($commercials as $commercial)
                                <option value="{{ $commercial->id }}" @selected($order->assigned_to === $commercial->id)>{{ $commercial->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-outline-primary">Asignar</button>
                    </form>
                    @error('assigned_to')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Cambio de estado --}}
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 mb-3">Estado</h2>
                    @if ($transitions === [])
                        <p class="text-body-secondary mb-0">Esta {{ mb_strtolower($order->type->label()) }} está en un estado final: ya no admite cambios.</p>
                    @else
                        <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                            @csrf
                            @method('PATCH')
                            <x-form-group name="note" label="Nota (opcional)" class="mb-3">
                                <textarea id="note" name="note" rows="2" maxlength="500" placeholder="Queda guardada en el historial"
                                    @class(['form-control', 'is-invalid' => $errors->has('note')])>{{ old('note') }}</textarea>
                            </x-form-group>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($transitions as $next)
                                    @php($blocked = $isQuote && $next === OrderStatus::Sent && $missingPrices > 0)
                                    <button type="submit" name="status" value="{{ $next->value }}"
                                        @class(['btn', 'btn-primary' => ! in_array($next, $destructive, true), 'btn-outline-danger' => in_array($next, $destructive, true)])
                                        @disabled($blocked)
                                        @if (in_array($next, $destructive, true)) data-confirm="¿{{ $next->actionLabel() }} {{ mb_strtolower($order->type->label()) }} {{ $order->code }}? Esta acción no se puede deshacer." @endif>
                                        {{ $next->actionLabel() }}
                                    </button>
                                @endforeach
                            </div>
                            @if ($isQuote && in_array(OrderStatus::Sent, $transitions, true) && $missingPrices > 0)
                                <p class="small text-body-secondary mt-2 mb-0">Para enviarla faltan los precios de {{ $missingPrices }} {{ $missingPrices === 1 ? 'producto' : 'productos' }}.</p>
                            @endif
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Productos: si se puede valorar, las celdas de precio son editables --}}
    <form method="POST" action="{{ route('admin.orders.prices', $order) }}" class="card mb-3">
        @csrf
        @method('PUT')
        <div class="card-body pb-2">
            <div class="card-title-row">
                <h2>Productos</h2>
                @if ($canPrice)
                    <span class="small text-body-secondary">Precio unitario con IVA incluido. Déjalo vacío si aún no lo tienes.</span>
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-dashboard align-middle">
                <thead>
                    <tr>
                        <th scope="col">Producto</th>
                        <th scope="col" class="text-end">Cantidad</th>
                        <th scope="col" class="text-end">Precio unitario</th>
                        <th scope="col" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->product_name }}</div>
                                <small class="text-body-secondary font-monospace">{{ $item->sku }}</small>
                                @if ($item->notes)
                                    <div><small>{{ $item->notes }}</small></div>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">{{ rtrim(rtrim($item->quantity, '0'), '.') }} {{ strtolower($item->unit->label()) }}</td>
                            <td class="text-end">
                                @if ($canPrice)
                                    <label for="price-{{ $item->id }}" class="visually-hidden">Precio unitario de {{ $item->product_name }}</label>
                                    <input type="number" id="price-{{ $item->id }}" name="prices[{{ $item->id }}]" min="0" step="0.01" placeholder="Sin precio"
                                        value="{{ old('prices.'.$item->id, $item->unit_price) }}"
                                        @class(['form-control form-control-sm text-end price-input', 'is-invalid' => $errors->has('prices.'.$item->id)])>
                                    @error('prices.'.$item->id)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                @else
                                    {{ Money::cop($item->unit_price, 'Por definir') }}
                                @endif
                            </td>
                            <td class="text-end">{{ Money::cop($item->line_total, 'Por definir') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end text-body-secondary">Subtotal (sin IVA)</td>
                        <td class="text-end">{{ Money::cop($order->subtotal, 'Por definir') }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end text-body-secondary">IVA</td>
                        <td class="text-end">{{ Money::cop($order->tax, 'Por definir') }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end text-body-secondary"><label for="shipping" class="mb-0">Envío</label></td>
                        <td class="text-end">
                            @if ($canPrice)
                                <input type="number" id="shipping" name="shipping" min="0" step="0.01" placeholder="0"
                                    value="{{ old('shipping', $order->shipping) }}"
                                    @class(['form-control form-control-sm text-end price-input ms-auto', 'is-invalid' => $errors->has('shipping')])>
                                @error('shipping')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @else
                                {{ Money::cop($order->shipping, 'Por definir') }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total</td>
                        <td class="text-end fw-bold">{{ Money::cop($order->total, 'Por definir') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @if ($canPrice)
            <div class="card-footer bg-white text-end">
                <button type="submit" class="btn btn-accent">Guardar precios</button>
            </div>
        @endif
    </form>

    {{-- Historial --}}
    <div class="card">
        <div class="card-body">
            <h2 class="h5 mb-3">Historial</h2>
            <ol class="list-unstyled mb-0">
                @foreach ($order->statusHistories->reverse() as $history)
                    <li class="d-flex gap-3 pb-3">
                        <span class="text-body-secondary small text-nowrap">{{ $history->created_at->format('d/m/Y H:i') }}</span>
                        <div>
                            @if ($history->from_status)
                                <x-status-badge :status="$history->from_status" /> <i class="bi bi-arrow-right mx-1" aria-hidden="true"></i>
                            @endif
                            <x-status-badge :status="$history->to_status" />
                            <div class="small text-body-secondary mt-1">
                                {{ $history->user?->name ?? 'Sistema' }}@if ($history->note) · {{ $history->note }}@endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
@endsection
