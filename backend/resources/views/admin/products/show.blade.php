@extends('layouts.admin')

@section('title', $product->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Productos</a></li>
    <li class="breadcrumb-item active text-truncate" aria-current="page">{{ $product->sku }}</li>
@endsection

@use('App\Support\Money')

@section('content')
    <div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1>{{ $product->name }}</h1>
            <p class="font-monospace">{{ $product->sku }}</p>
        </div>
        @unless ($product->trashed())
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Editar</a>
        @endunless
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Marca</dt>
                        <dd class="col-sm-8">{{ $product->brand->name }}</dd>
                        <dt class="col-sm-4">Categoría</dt>
                        <dd class="col-sm-8">{{ $product->category->name }}</dd>
                        <dt class="col-sm-4">Precio (IVA incluido)</dt>
                        <dd class="col-sm-8">{{ Money::cop($product->price, 'Solo cotización') }} <small class="text-body-secondary">· IVA {{ rtrim(rtrim((string) $product->tax_rate, '0'), '.') }}%</small></dd>
                        <dt class="col-sm-4">Stock</dt>
                        <dd class="col-sm-8">
                            {{ $product->stock }} {{ strtolower($product->unit->label()) }}
                            @if ($product->stock <= 0)
                                <span class="stock-badge is-out ms-1">Agotado</span>
                            @elseif ($product->stock <= $lowStockThreshold)
                                <span class="stock-badge ms-1">Stock bajo</span>
                            @endif
                        </dd>
                        <dt class="col-sm-4">Modalidad</dt>
                        <dd class="col-sm-8">{{ $product->sale_mode->label() }}</dd>
                        <dt class="col-sm-4">Estado</dt>
                        <dd class="col-sm-8">
                            {{ $product->trashed() ? 'Eliminado' : ($product->is_active ? 'Activo' : 'Inactivo') }}
                            @if ($product->is_featured)
                                · Destacado
                            @endif
                        </dd>
                        <dt class="col-sm-4">Descripción corta</dt>
                        <dd class="col-sm-8">{{ $product->short_description }}</dd>
                        @if ($product->description)
                            <dt class="col-sm-4">Descripción</dt>
                            <dd class="col-sm-8">{!! nl2br(e($product->description)) !!}</dd>
                        @endif
                        @if ($product->specs)
                            <dt class="col-sm-4">Especificaciones</dt>
                            <dd class="col-sm-8 mb-0">
                                <ul class="list-unstyled mb-0">
                                    @foreach ($product->specs as $key => $value)
                                        <li><span class="font-monospace text-body-secondary">{{ $key }}</span>: {{ $value }}</li>
                                    @endforeach
                                </ul>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    @if ($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-fluid" referrerpolicy="no-referrer">
                    @else
                        <x-empty-state icon="image" title="Sin imagen" />
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
