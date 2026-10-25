@extends('layouts.admin')

@section('title', 'Búsqueda')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Búsqueda</li>
@endsection

@php($isAdmin = auth()->user()->hasRole(\App\Enums\UserRole::Admin))

@section('content')
    <div class="page-header">
        <h1>Búsqueda</h1>
        @if ($products)
            <p>{{ $products->total() }} {{ $products->total() === 1 ? 'producto encontrado' : 'productos encontrados' }} para «{{ $term }}»</p>
        @endif
    </div>

    <div class="card">
        @if ($products === null)
            <x-empty-state icon="search" title="Escribe qué buscas"
                :message="'Usa al menos '.$minLength.' letras del nombre o de la referencia del producto.'" />
        @elseif ($products->isEmpty())
            <x-empty-state icon="search" title="Sin resultados"
                message="No hay productos con ese nombre o referencia. Prueba con otra palabra." />
        @else
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
        @endif
    </div>
@endsection
