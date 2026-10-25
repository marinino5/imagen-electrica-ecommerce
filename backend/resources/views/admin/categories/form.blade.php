@extends('layouts.admin')

@php($editing = $category->exists)

@section('title', $editing ? 'Editar categoría' : 'Nueva categoría')

@section('breadcrumb')
    <li class="breadcrumb-item">Catálogo</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categorías</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Editar' : 'Nueva' }}</li>
@endsection

@section('content')
    <div class="page-header">
        <h1>{{ $editing ? 'Editar categoría' : 'Nueva categoría' }}</h1>
        @if ($editing)
            <p class="font-monospace">{{ $category->slug }}</p>
        @endif
    </div>

    <form method="POST" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="card form-card" novalidate>
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card-body">
            @if ($editing)
                <div class="d-flex align-items-center gap-3 mb-4">
                    @if ($thumbnail = $category->thumbnailUrl())
                        <img src="{{ $thumbnail }}" alt="Imagen de {{ $category->name }}" class="thumb thumb-lg">
                    @else
                        <span class="thumb thumb-lg thumb-empty" aria-hidden="true"><i class="bi bi-image fs-3"></i></span>
                    @endif
                    <small class="text-body-secondary">La imagen sale de <span class="font-monospace">public/images/categorias/{{ $category->slug }}.jpg</span>
                        (las subcategorías sin imagen usan la de su categoría padre).</small>
                </div>
            @endif

            <x-form-group name="name" label="Nombre" required>
                <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" maxlength="255" required autofocus
                    @class(['form-control', 'is-invalid' => $errors->has('name')])>
            </x-form-group>

            <x-form-group name="parent_id" label="Categoría padre" help="Déjala vacía si es una categoría de primer nivel.">
                <select id="parent_id" name="parent_id" @class(['form-select', 'is-invalid' => $errors->has('parent_id')])>
                    <option value="">— Sin padre (primer nivel) —</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $parent->id)>{{ $parent->name }}</option>
                    @endforeach
                </select>
            </x-form-group>

            <x-form-group name="description" label="Descripción">
                <textarea id="description" name="description" rows="3" maxlength="2000"
                    @class(['form-control', 'is-invalid' => $errors->has('description')])>{{ old('description', $category->description) }}</textarea>
            </x-form-group>

            <div class="row">
                <x-form-group name="sort_order" label="Orden" class="col-sm-4" help="Menor número aparece primero.">
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0" max="65535"
                        @class(['form-control', 'is-invalid' => $errors->has('sort_order')])>
                </x-form-group>
            </div>

            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $category->is_active))>
                <label class="form-check-label" for="is_active">Categoría activa</label>
            </div>
        </div>

        <div class="card-footer bg-white d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Guardar cambios' : 'Crear categoría' }}</button>
        </div>
    </form>
@endsection
