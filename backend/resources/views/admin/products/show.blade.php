@extends('layouts.admin')

@section('title', $product->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Productos</a></li>
    <li class="breadcrumb-item active text-truncate" aria-current="page">{{ $product->sku }}</li>
@endsection

@section('content')
    <div class="page-header">
        <h1>{{ $product->name }}</h1>
        <p class="font-monospace">{{ $product->sku }}</p>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Marca</dt>
                <dd class="col-sm-9">{{ $product->brand->name }}</dd>
                <dt class="col-sm-3">Categoría</dt>
                <dd class="col-sm-9">{{ $product->category->name }}</dd>
                <dt class="col-sm-3">Precio</dt>
                <dd class="col-sm-9">{{ $product->price === null ? 'Solo cotización' : '$ '.number_format((float) $product->price, 0, ',', '.') }}</dd>
                <dt class="col-sm-3">Stock</dt>
                <dd class="col-sm-9">{{ $product->stock }} {{ $product->unit->label() }}</dd>
                <dt class="col-sm-3">Modalidad</dt>
                <dd class="col-sm-9">{{ $product->sale_mode->label() }}</dd>
                <dt class="col-sm-3">Estado</dt>
                <dd class="col-sm-9 mb-0">{{ $product->is_active ? 'Activo' : 'Inactivo' }}</dd>
            </dl>
        </div>
    </div>
@endsection
