@extends('layouts.admin')

@section('title', 'Búsqueda')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Búsqueda</li>
@endsection

@use('App\Support\Money')

@php
    $isAdmin = auth()->user()->hasRole(\App\Enums\UserRole::Admin);
    $products = $results['products'] ?? null;
    $orders = $results['orders'] ?? collect();
    $customers = $results['customers'] ?? collect();
    $total = $results ? $products->total() + $orders->count() + $customers->count() : 0;
@endphp

@section('content')
    <div class="page-header">
        <h1>Búsqueda</h1>
        @if ($results)
            <p>{{ $total }} {{ $total === 1 ? 'resultado' : 'resultados' }} para «{{ $term }}» en productos, pedidos, cotizaciones y clientes.</p>
        @endif
    </div>

    @if ($results === null)
        <div class="card"><x-empty-state icon="search" title="Escribe qué buscas"
            :message="'Usa al menos '.$minLength.' letras: nombre o referencia de un producto, código de pedido o cotización, o empresa, contacto, documento o correo de un cliente.'" /></div>
    @elseif ($total === 0)
        <div class="card"><x-empty-state icon="search" title="Sin resultados" message="No encontramos nada con esa palabra. Prueba con otra o con menos letras." /></div>
    @else
        @if ($orders->isNotEmpty())
            <div class="card mb-3">
                <div class="card-body border-bottom"><h2 class="h5 mb-0">Pedidos y cotizaciones <span class="badge text-bg-light border ms-1">{{ $orders->count() }}</span></h2></div>
                <div class="table-responsive">
                    <table class="table table-dashboard align-middle">
                        <thead><tr><th scope="col">Código</th><th scope="col">Cliente</th><th scope="col">Estado</th><th scope="col" class="text-end">Total</th></tr></thead>
                        <tbody>
                            @foreach ($orders as $o)
                                <tr>
                                    <td class="font-monospace"><a href="{{ route('admin.orders.show', $o) }}" class="fw-semibold text-decoration-none">{{ $o->code }}</a></td>
                                    <td>{{ $o->display_name }}<div class="small text-body-secondary">{{ $o->email }}</div></td>
                                    <td><x-status-badge :status="$o->status" /></td>
                                    <td class="text-end">{{ Money::cop($o->total, 'Por definir') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($customers->isNotEmpty())
            <div class="card mb-3">
                <div class="card-body border-bottom"><h2 class="h5 mb-0">Clientes <span class="badge text-bg-light border ms-1">{{ $customers->count() }}</span></h2></div>
                <div class="table-responsive">
                    <table class="table table-dashboard align-middle">
                        <thead><tr><th scope="col">Cliente</th><th scope="col">Documento</th><th scope="col" class="d-none d-md-table-cell">Contacto</th></tr></thead>
                        <tbody>
                            @foreach ($customers as $c)
                                <tr>
                                    <td><a href="{{ route('admin.customers.show', $c) }}" class="fw-semibold text-decoration-none">{{ $c->company_name ?: $c->contact_name }}</a><div class="small text-body-secondary">{{ $c->city }}</div></td>
                                    <td><x-document :document="$c->document" /></td>
                                    <td class="d-none d-md-table-cell">{{ $c->contact_name }}<div class="small text-body-secondary">{{ $c->user?->email }}</div></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($products->isNotEmpty())
            <div class="card">
                <div class="card-body border-bottom"><h2 class="h5 mb-0">Productos <span class="badge text-bg-light border ms-1">{{ $products->total() }}</span></h2></div>
                <div class="table-responsive">
                    <table class="table table-dashboard">
                        <thead>
                            <tr>
                                <th scope="col">Producto</th>
                                <th scope="col">Marca</th>
                                <th scope="col">Categoría</th>
                                <th scope="col" class="text-end">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>
                                        {{-- Solo admin puede abrir la ficha del producto --}}
                                        @if ($isAdmin)
                                            <a href="{{ route('admin.products.show', $product) }}" class="fw-semibold text-decoration-none">{{ $product->name }}</a>
                                        @else
                                            <span class="fw-semibold">{{ $product->name }}</span>
                                        @endif
                                        @unless ($product->is_active)
                                            <span class="status-badge status-cancelled ms-1">Inactivo</span>
                                        @endunless
                                        <div><small class="text-body-secondary font-monospace">{{ $product->sku }}</small></div>
                                    </td>
                                    <td>{{ $product->brand->name }}</td>
                                    <td>{{ $product->category->name }}</td>
                                    <td class="text-end">{{ $product->stock }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($products->hasPages())
                    <div class="card-body border-top">{{ $products->links() }}</div>
                @endif
            </div>
        @endif
    @endif
@endsection
