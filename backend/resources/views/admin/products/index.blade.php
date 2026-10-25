@extends('layouts.admin')

@section('title', 'Productos')

@section('breadcrumb')
    <li class="breadcrumb-item">Catálogo</li>
    <li class="breadcrumb-item active" aria-current="page">Productos</li>
@endsection

@use('App\Support\Money')

@section('content')
    <div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1>Productos</h1>
            <p>Cada referencia de Siigo es un producto.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nuevo producto</a>
    </div>

    <div class="card">
        <div class="card-body border-bottom">
            <x-search-bar :action="route('admin.products.index')" :value="$filters['q']" placeholder="Buscar por nombre o referencia…">
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="category_id" class="visually-hidden">Categoría</label>
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="">Todas las categorías</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($filters['category_id'] === $category->id)>{{ $category->parent_id ? '— ' : '' }}{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="brand_id" class="visually-hidden">Marca</label>
                    <select id="brand_id" name="brand_id" class="form-select">
                        <option value="">Todas las marcas</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected($filters['brand_id'] === $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="sale_mode" class="visually-hidden">Modalidad de venta</label>
                    <select id="sale_mode" name="sale_mode" class="form-select">
                        <option value="">Toda modalidad</option>
                        @foreach (\App\Enums\SaleMode::cases() as $mode)
                            <option value="{{ $mode->value }}" @selected($filters['sale_mode'] === $mode->value)>{{ $mode->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="stock" class="visually-hidden">Stock</label>
                    <select id="stock" name="stock" class="form-select">
                        <option value="">Todo el stock</option>
                        <option value="low" @selected($filters['stock'] === 'low')>Stock bajo (≤ {{ $lowStockThreshold }})</option>
                        <option value="out" @selected($filters['stock'] === 'out')>Agotado</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="status" class="visually-hidden">Estado</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="active" @selected($filters['status'] === 'active')>Activos</option>
                        <option value="inactive" @selected($filters['status'] === 'inactive')>Inactivos</option>
                        <option value="trashed" @selected($filters['status'] === 'trashed')>Eliminados</option>
                    </select>
                </div>
            </x-search-bar>
        </div>

        @if ($products->isEmpty())
            <x-empty-state icon="box-seam" :title="request()->query() ? 'Sin resultados' : 'Aún no hay productos'"
                :message="request()->query() ? 'Ningún producto cumple esos filtros. Prueba con otros.' : 'Crea el primero con el botón «Nuevo producto».'" />
        @else
            <div class="table-responsive">
                <table class="table table-dashboard align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="w-1"><span class="visually-hidden">Imagen</span></th>
                            <th scope="col">Producto</th>
                            <th scope="col" class="d-none d-lg-table-cell">Categoría</th>
                            <th scope="col" class="d-none d-xl-table-cell">Modalidad</th>
                            <th scope="col" class="text-end d-none d-md-table-cell">Precio</th>
                            <th scope="col" class="text-end">Stock</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>
                                    @if ($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="" class="thumb thumb-contain" loading="lazy" referrerpolicy="no-referrer">
                                    @else
                                        <span class="thumb thumb-empty" aria-hidden="true"><i class="bi bi-image"></i></span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.show', $product) }}" class="fw-semibold text-decoration-none">{{ $product->name }}</a>
                                    @unless ($product->is_active)
                                        <span class="status-badge status-cancelled ms-1">Inactivo</span>
                                    @endunless
                                    @if ($product->trashed())
                                        <span class="status-badge status-rejected ms-1">Eliminado</span>
                                    @endif
                                    <div class="text-body-secondary small">
                                        <span class="font-monospace">{{ $product->sku }}</span> · {{ $product->brand->name }}
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">{{ $product->category->name }}</td>
                                <td class="d-none d-xl-table-cell">{{ $product->sale_mode->label() }}</td>
                                <td class="text-end d-none d-md-table-cell">{{ Money::cop($product->price, 'Cotizar') }}</td>
                                <td class="text-end text-nowrap">
                                    @if ($product->stock <= 0)
                                        <span class="stock-badge is-out">Agotado</span>
                                    @elseif ($product->stock <= $lowStockThreshold)
                                        <span class="stock-badge">{{ $product->stock }}</span>
                                    @else
                                        {{ $product->stock }}
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @if ($product->trashed())
                                        <form method="POST" action="{{ route('admin.products.restore', $product) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>Restaurar<span class="visually-hidden"> {{ $product->name }}</span></button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil" aria-hidden="true"></i><span class="visually-hidden">Editar {{ $product->name }}</span>
                                        </a>
                                        <x-delete-form :action="route('admin.products.destroy', $product)" :label="$product->name"
                                            :confirm="'¿Eliminar el producto «'.$product->name.'»? Podrás restaurarlo después.'" />
                                    @endif
                                </td>
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
