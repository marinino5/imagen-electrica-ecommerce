@extends('layouts.admin')

@section('title', 'Categorías')

@section('breadcrumb')
    <li class="breadcrumb-item">Catálogo</li>
    <li class="breadcrumb-item active" aria-current="page">Categorías</li>
@endsection

@section('content')
    <div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1>Categorías</h1>
            <p>Líneas de producto y subcategorías del catálogo.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nueva categoría</a>
    </div>

    <div class="card">
        <div class="card-body border-bottom">
            <x-search-bar :action="route('admin.categories.index')" :value="$search" placeholder="Buscar por nombre o slug…" />
        </div>

        @if ($categories->isEmpty())
            <x-empty-state icon="diagram-3" :title="$search ? 'Sin resultados' : 'Aún no hay categorías'"
                :message="$search ? 'No hay categorías con ese nombre. Prueba con otra palabra.' : 'Crea la primera con el botón «Nueva categoría».'" />
        @else
            <div class="table-responsive">
                <table class="table table-dashboard align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="w-1"><span class="visually-hidden">Imagen</span></th>
                            <th scope="col">Nombre</th>
                            <th scope="col" class="d-none d-md-table-cell">Slug</th>
                            <th scope="col" class="text-end">Productos</th>
                            <th scope="col" class="d-none d-sm-table-cell text-end">Orden</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td>
                                    @if ($thumbnail = $category->thumbnailUrl())
                                        <img src="{{ $thumbnail }}" alt="" class="thumb" loading="lazy">
                                    @else
                                        <span class="thumb thumb-empty" aria-hidden="true"><i class="bi bi-image"></i></span>
                                    @endif
                                </td>
                                <td>
                                    @if ($category->parent)
                                        <small class="text-body-secondary d-block">{{ $category->parent->name }} ›</small>
                                    @endif
                                    <span class="fw-semibold">{{ $category->name }}</span>
                                </td>
                                <td class="d-none d-md-table-cell"><small class="text-body-secondary font-monospace">{{ $category->slug }}</small></td>
                                <td class="text-end">{{ $category->products_count }}</td>
                                <td class="d-none d-sm-table-cell text-end">{{ $category->sort_order }}</td>
                                <td>
                                    <span @class(['status-badge', 'status-paid' => $category->is_active, 'status-cancelled' => ! $category->is_active])>{{ $category->is_active ? 'Activa' : 'Inactiva' }}</span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil" aria-hidden="true"></i><span class="visually-hidden">Editar {{ $category->name }}</span>
                                    </a>
                                    <x-delete-form :action="route('admin.categories.destroy', $category)" :label="$category->name"
                                        :confirm="'¿Eliminar la categoría «'.$category->name.'»?'" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($categories->hasPages())
                <div class="card-body border-top">{{ $categories->links() }}</div>
            @endif
        @endif
    </div>
@endsection
