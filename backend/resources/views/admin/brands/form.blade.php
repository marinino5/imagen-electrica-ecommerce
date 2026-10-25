@extends('layouts.admin')

@php($editing = $brand->exists)

@section('title', $editing ? 'Editar marca' : 'Nueva marca')

@section('breadcrumb')
    <li class="breadcrumb-item">Catálogo</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.brands.index') }}">Marcas</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Editar' : 'Nueva' }}</li>
@endsection

@section('content')
    <div class="page-header">
        <h1>{{ $editing ? 'Editar marca' : 'Nueva marca' }}</h1>
        @if ($editing)
            <p class="font-monospace">{{ $brand->slug }}</p>
        @endif
    </div>

    <form method="POST" action="{{ $editing ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" class="card form-card" novalidate>
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card-body">
            <x-form-group name="name" label="Nombre" required>
                <input type="text" id="name" name="name" value="{{ old('name', $brand->name) }}" maxlength="255" required autofocus
                    @class(['form-control', 'is-invalid' => $errors->has('name')])>
            </x-form-group>

            <x-form-group name="logo_url" label="URL del logo" help="Dirección pública de la imagen (https://…). Es opcional.">
                <input type="url" id="logo_url" name="logo_url" value="{{ old('logo_url', $brand->logo_url) }}" maxlength="2048" placeholder="https://"
                    @class(['form-control', 'is-invalid' => $errors->has('logo_url')])>
            </x-form-group>

            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $brand->is_active))>
                <label class="form-check-label" for="is_active">Marca activa</label>
            </div>
        </div>

        <div class="card-footer bg-white d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Guardar cambios' : 'Crear marca' }}</button>
        </div>
    </form>
@endsection
