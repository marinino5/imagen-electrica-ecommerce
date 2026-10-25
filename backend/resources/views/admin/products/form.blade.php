@extends('layouts.admin')

@php
    $editing = $product->exists;

    // Filas del editor de especificaciones: lo que se escribió (si hubo error) o lo guardado
    $specRows = old('specs');
    if ($specRows === null) {
        $specRows = collect($product->specs ?? [])->map(fn ($value, $key) => ['key' => $key, 'value' => $value])->values()->all();
    }
    if ($specRows === []) {
        $specRows = [['key' => '', 'value' => '']];
    }
@endphp

@section('title', $editing ? 'Editar producto' : 'Nuevo producto')

@section('breadcrumb')
    <li class="breadcrumb-item">Catálogo</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Productos</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Editar' : 'Nuevo' }}</li>
@endsection

@section('content')
    <div class="page-header">
        <h1>{{ $editing ? 'Editar producto' : 'Nuevo producto' }}</h1>
        @if ($editing)
            <p class="font-monospace">{{ $product->sku }}</p>
        @endif
    </div>

    <form method="POST" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}" class="card form-card form-card-wide" novalidate>
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card-body">
            <h2 class="h5 mb-3">Datos generales</h2>
            <div class="row">
                <x-form-group name="sku" label="Referencia (SKU)" class="col-md-4" required help="Igual al código de Siigo.">
                    <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" maxlength="50" required autofocus
                        @class(['form-control font-monospace', 'is-invalid' => $errors->has('sku')])>
                </x-form-group>
                <x-form-group name="name" label="Nombre" class="col-md-8" required>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" maxlength="255" required
                        @class(['form-control', 'is-invalid' => $errors->has('name')])>
                </x-form-group>
            </div>
            <div class="row">
                <x-form-group name="category_id" label="Categoría" class="col-md-6" required>
                    <select id="category_id" name="category_id" required @class(['form-select', 'is-invalid' => $errors->has('category_id')])>
                        <option value="">Selecciona…</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->parent ? $category->parent->name.' › ' : '' }}{{ $category->name }}</option>
                        @endforeach
                    </select>
                </x-form-group>
                <x-form-group name="brand_id" label="Marca" class="col-md-6" required>
                    <select id="brand_id" name="brand_id" required @class(['form-select', 'is-invalid' => $errors->has('brand_id')])>
                        <option value="">Selecciona…</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected((string) old('brand_id', $product->brand_id) === (string) $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </x-form-group>
            </div>
            <x-form-group name="short_description" label="Descripción corta" required>
                <input type="text" id="short_description" name="short_description" value="{{ old('short_description', $product->short_description) }}" maxlength="500" required
                    @class(['form-control', 'is-invalid' => $errors->has('short_description')])>
            </x-form-group>
            <x-form-group name="description" label="Descripción">
                <textarea id="description" name="description" rows="4" maxlength="5000"
                    @class(['form-control', 'is-invalid' => $errors->has('description')])>{{ old('description', $product->description) }}</textarea>
            </x-form-group>
            <x-form-group name="image_url" label="URL de la imagen" help="Dirección pública de la foto (https://…). No se suben archivos al servidor.">
                <input type="url" id="image_url" name="image_url" value="{{ old('image_url', $product->image_url) }}" maxlength="2048" placeholder="https://"
                    @class(['form-control', 'is-invalid' => $errors->has('image_url')])>
            </x-form-group>

            <hr class="my-4">
            <h2 class="h5 mb-3">Venta e inventario</h2>
            <div class="row">
                <x-form-group name="sale_mode" label="Modalidad de venta" class="col-md-4" required
                    help="Política comercial: no cambia cuando el stock llega a cero.">
                    <select id="sale_mode" name="sale_mode" required @class(['form-select', 'is-invalid' => $errors->has('sale_mode')])>
                        @foreach ($saleModes as $mode)
                            <option value="{{ $mode->value }}" @selected((old('sale_mode', $product->sale_mode?->value)) === $mode->value)>{{ $mode->label() }}</option>
                        @endforeach
                    </select>
                </x-form-group>
                <x-form-group name="unit" label="Unidad" class="col-md-4" required>
                    <select id="unit" name="unit" required @class(['form-select', 'is-invalid' => $errors->has('unit')])>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->value }}" @selected((old('unit', $product->unit?->value)) === $unit->value)>{{ $unit->label() }}</option>
                        @endforeach
                    </select>
                </x-form-group>
                <x-form-group name="stock" label="Stock" class="col-md-4" required>
                    <input type="number" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" min="0" step="1" required
                        @class(['form-control', 'is-invalid' => $errors->has('stock')])>
                </x-form-group>
            </div>
            <div class="row">
                <x-form-group name="price" label="Precio (COP, IVA incluido)" class="col-md-6"
                    help="Déjalo vacío si el producto solo se vende por cotización.">
                    <input type="number" id="price" name="price" value="{{ old('price', $product->price) }}" min="0" step="0.01"
                        @class(['form-control', 'is-invalid' => $errors->has('price')])>
                </x-form-group>
                <x-form-group name="tax_rate" label="IVA (%)" class="col-md-6" required>
                    <input type="number" id="tax_rate" name="tax_rate" value="{{ old('tax_rate', $product->tax_rate) }}" min="0" max="100" step="0.01" required
                        @class(['form-control', 'is-invalid' => $errors->has('tax_rate')])>
                </x-form-group>
            </div>
            <div class="row">
                <x-form-group name="cost_price" label="Costo (COP, sin IVA)" class="col-md-6"
                    help="Dato interno para calcular la ganancia en Reportes. No se muestra en la tienda.">
                    <input type="number" id="cost_price" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" min="0" step="0.01"
                        @class(['form-control', 'is-invalid' => $errors->has('cost_price')])>
                </x-form-group>
            </div>
            <div class="d-flex flex-wrap gap-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
                    <label class="form-check-label" for="is_active">Producto activo</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>
                    <label class="form-check-label" for="is_featured">Destacado</label>
                </div>
            </div>

            <hr class="my-4">
            <h2 class="h5 mb-1">Especificaciones</h2>
            <p class="text-body-secondary small">Nombre en minúsculas con la unidad al final y valor, por ejemplo <span class="font-monospace">potencia_hp</span> = 5.</p>

            @error('specs')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror

            <div data-specs-editor>
                <div data-specs-rows>
                    @foreach ($specRows as $i => $row)
                        @include('admin.products.partials.spec-row', ['index' => $i, 'row' => $row])
                    @endforeach
                </div>
                <template>
                    @include('admin.products.partials.spec-row', ['index' => '__INDEX__', 'row' => ['key' => '', 'value' => '']])
                </template>
                <button type="button" class="btn btn-sm btn-outline-primary" data-specs-add>
                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Agregar especificación
                </button>
            </div>
        </div>

        <div class="card-footer bg-white d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Guardar cambios' : 'Crear producto' }}</button>
        </div>
    </form>
@endsection
