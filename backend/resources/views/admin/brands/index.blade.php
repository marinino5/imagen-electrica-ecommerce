@extends('layouts.admin')

@section('title', 'Marcas')

@section('breadcrumb')
    <li class="breadcrumb-item">Catálogo</li>
    <li class="breadcrumb-item active" aria-current="page">Marcas</li>
@endsection

@section('content')
    <div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1>Marcas</h1>
            <p>Fabricantes de los productos del catálogo.</p>
        </div>
        <a href="{{ route('admin.brands.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nueva marca</a>
    </div>

    <div class="card">
        <div class="card-body border-bottom">
            <x-search-bar :action="route('admin.brands.index')" :value="$search" placeholder="Buscar por nombre…" />
        </div>

        @if ($brands->isEmpty())
            <x-empty-state icon="award" :title="$search ? 'Sin resultados' : 'Aún no hay marcas'"
                :message="$search ? 'No hay marcas con ese nombre. Prueba con otra palabra.' : 'Crea la primera con el botón «Nueva marca».'" />
        @else
            <div class="table-responsive">
                <table class="table table-dashboard align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="w-1"><span class="visually-hidden">Logo</span></th>
                            <th scope="col">Nombre</th>
                            <th scope="col" class="d-none d-md-table-cell">Slug</th>
                            <th scope="col" class="text-end">Productos</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($brands as $brand)
                            <tr>
                                <td>
                                    @if ($brand->logo_url)
                                        <img src="{{ $brand->logo_url }}" alt="" class="thumb thumb-contain" loading="lazy">
                                    @else
                                        <span class="thumb thumb-empty" aria-hidden="true"><i class="bi bi-award"></i></span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $brand->name }}</td>
                                <td class="d-none d-md-table-cell"><small class="text-body-secondary font-monospace">{{ $brand->slug }}</small></td>
                                <td class="text-end">{{ $brand->products_count }}</td>
                                <td>
                                    <span @class(['status-badge', 'status-paid' => $brand->is_active, 'status-cancelled' => ! $brand->is_active])>{{ $brand->is_active ? 'Activa' : 'Inactiva' }}</span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil" aria-hidden="true"></i><span class="visually-hidden">Editar {{ $brand->name }}</span>
                                    </a>
                                    <x-delete-form :action="route('admin.brands.destroy', $brand)" :label="$brand->name"
                                        :confirm="'¿Eliminar la marca «'.$brand->name.'»?'" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($brands->hasPages())
                <div class="card-body border-top">{{ $brands->links() }}</div>
            @endif
        @endif
    </div>
@endsection
