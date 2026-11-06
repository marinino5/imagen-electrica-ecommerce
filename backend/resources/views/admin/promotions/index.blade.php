@extends('layouts.admin')

@section('title', 'Promociones')

@section('breadcrumb')
    <li class="breadcrumb-item">Catálogo</li>
    <li class="breadcrumb-item active" aria-current="page">Promociones</li>
@endsection

@section('content')
    <div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1>Promociones</h1>
            <p>Lo que se muestra en «Promociones del momento» del Inicio de la tienda. Si no hay ninguna vigente, la sección se oculta.</p>
        </div>
        <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nueva promoción</a>
    </div>

    <div class="card">
        <div class="card-body border-bottom">
            <x-search-bar :action="route('admin.promotions.index')" :value="$search" placeholder="Buscar por título o línea…" />
        </div>

        @if ($promotions->isEmpty())
            <x-empty-state icon="tags" :title="$search ? 'Sin resultados' : 'Aún no hay promociones'"
                :message="$search ? 'No hay promociones con ese título o línea.' : 'Crea la primera con el botón «Nueva promoción».'" />
        @else
            <div class="table-responsive">
                <table class="table table-dashboard align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Promoción</th>
                            <th scope="col" class="d-none d-md-table-cell">Aplica a</th>
                            <th scope="col">Oferta</th>
                            <th scope="col" class="d-none d-lg-table-cell">Vigencia</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($promotions as $promotion)
                            @php
                                $state = match (true) {
                                    ! $promotion->is_active => ['Inactiva', 'status-cancelled'],
                                    $promotion->ends_at->lt(today()) => ['Vencida', 'status-cancelled'],
                                    $promotion->starts_at->gt(today()) => ['Programada', 'status-pending'],
                                    default => ['Vigente', 'status-paid'],
                                };
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $promotion->title }}</td>
                                <td class="d-none d-md-table-cell">
                                    {{ $promotion->line?->name }}
                                    @if ($promotion->product)
                                        <small class="text-body-secondary font-monospace d-block">{{ $promotion->product->sku }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($promotion->special_price !== null)
                                        ${{ number_format((float) $promotion->special_price, 0, ',', '.') }}
                                    @else
                                        {{ rtrim(rtrim(number_format((float) $promotion->discount_percent, 2, ',', ''), '0'), ',') }} %
                                    @endif
                                </td>
                                <td class="d-none d-lg-table-cell text-nowrap">{{ $promotion->starts_at->format('d/m/Y') }} – {{ $promotion->ends_at->format('d/m/Y') }}</td>
                                <td><span class="status-badge {{ $state[1] }}">{{ $state[0] }}</span></td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil" aria-hidden="true"></i><span class="visually-hidden">Editar {{ $promotion->title }}</span>
                                    </a>
                                    <x-delete-form :action="route('admin.promotions.destroy', $promotion)" :label="$promotion->title"
                                        :confirm="'¿Eliminar la promoción «'.$promotion->title.'»?'" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($promotions->hasPages())
                <div class="card-body border-top">{{ $promotions->links() }}</div>
            @endif
        @endif
    </div>
@endsection
