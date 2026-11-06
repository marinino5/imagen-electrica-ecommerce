@extends('layouts.admin')

@php($editing = $promotion->exists)

@section('title', $editing ? 'Editar promoción' : 'Nueva promoción')

@section('breadcrumb')
    <li class="breadcrumb-item">Catálogo</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.promotions.index') }}">Promociones</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Editar' : 'Nueva' }}</li>
@endsection

@section('content')
    <div class="page-header">
        <h1>{{ $editing ? 'Editar promoción' : 'Nueva promoción' }}</h1>
    </div>

    <form method="POST" action="{{ $editing ? route('admin.promotions.update', $promotion) : route('admin.promotions.store') }}" class="card form-card" novalidate>
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card-body">
            <x-form-group name="title" label="Título" required>
                <input type="text" id="title" name="title" value="{{ old('title', $promotion->title) }}" maxlength="120" required autofocus
                    @class(['form-control', 'is-invalid' => $errors->has('title')])>
            </x-form-group>

            <x-form-group name="description" label="Descripción corta" help="Una frase opcional bajo el título (máximo 255 caracteres).">
                <input type="text" id="description" name="description" value="{{ old('description', $promotion->description) }}" maxlength="255"
                    @class(['form-control', 'is-invalid' => $errors->has('description')])>
            </x-form-group>

            <div class="row g-3">
                <div class="col-md-8">
                    <x-form-group name="product_line_id" label="Línea" required>
                        <select id="product_line_id" name="product_line_id" required @class(['form-select', 'is-invalid' => $errors->has('product_line_id')])>
                            <option value="">Elige una línea…</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line->id }}" @selected((string) old('product_line_id', $promotion->product_line_id) === (string) $line->id)>{{ $line->name }}</option>
                            @endforeach
                        </select>
                    </x-form-group>
                </div>
                <div class="col-md-4">
                    <x-form-group name="sku" label="SKU (opcional)" help="Solo si la promoción es de una referencia.">
                        <input type="text" id="sku" name="sku" value="{{ old('sku', $promotion->product?->sku) }}" maxlength="60" spellcheck="false" autocomplete="off"
                            @class(['form-control', 'font-monospace', 'is-invalid' => $errors->has('sku')])>
                    </x-form-group>
                </div>
            </div>

            <fieldset class="mb-3">
                <legend class="form-label fw-semibold fs-6">Oferta <span class="text-danger" aria-hidden="true">*</span></legend>
                <div class="row g-3">
                    <div class="col-md-6">
                        <x-form-group name="discount_percent" label="Descuento (%)" class="mb-0">
                            <input type="number" id="discount_percent" name="discount_percent" value="{{ old('discount_percent', $promotion->discount_percent) }}" min="0.01" max="90" step="0.01" inputmode="decimal"
                                @class(['form-control', 'is-invalid' => $errors->has('discount_percent')])>
                        </x-form-group>
                    </div>
                    <div class="col-md-6">
                        <x-form-group name="special_price" label="Precio especial (COP, IVA incluido)" class="mb-0">
                            <input type="number" id="special_price" name="special_price" value="{{ old('special_price', $promotion->special_price) }}" min="1" step="1" inputmode="numeric"
                                @class(['form-control', 'is-invalid' => $errors->has('special_price')])>
                        </x-form-group>
                    </div>
                </div>
                <div class="form-text">Llena solo uno de los dos.</div>
            </fieldset>

            <div class="row g-3">
                <div class="col-md-6">
                    <x-form-group name="starts_at" label="Desde" required>
                        <input type="date" id="starts_at" name="starts_at" value="{{ old('starts_at', $promotion->starts_at?->format('Y-m-d')) }}" required
                            @class(['form-control', 'is-invalid' => $errors->has('starts_at')])>
                    </x-form-group>
                </div>
                <div class="col-md-6">
                    <x-form-group name="ends_at" label="Hasta (incluido)" required>
                        <input type="date" id="ends_at" name="ends_at" value="{{ old('ends_at', $promotion->ends_at?->format('Y-m-d')) }}" required
                            @class(['form-control', 'is-invalid' => $errors->has('ends_at')])>
                    </x-form-group>
                </div>
            </div>

            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $promotion->is_active))>
                <label class="form-check-label" for="is_active">Promoción activa</label>
            </div>
        </div>

        <div class="card-footer bg-white d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Guardar cambios' : 'Crear promoción' }}</button>
        </div>
    </form>
@endsection
