@extends('layouts.admin')

@section('title', 'Fichas descargadas')

@section('breadcrumb')
    <li class="breadcrumb-item">General</li>
    <li class="breadcrumb-item active" aria-current="page">Fichas descargadas</li>
@endsection

@section('content')
    <div class="page-header">
        <h1>Fichas descargadas</h1>
        <p>Fichas técnicas con cotización en PDF que descargaron los visitantes de la tienda: qué producto, referencia y cantidad miraron.</p>
    </div>

    <div class="card">
        <div class="card-body border-bottom">
            <x-search-bar :action="route('admin.datasheets.index')" :value="$search" placeholder="Buscar por número, referencia o producto" />
        </div>

        @if ($downloads->isEmpty())
            <x-empty-state icon="file-earmark-pdf" title="Aún no hay fichas descargadas" message="Cuando alguien use «Descargar ficha y cotización» en la tienda, aparecerá aquí." />
        @else
            <div class="table-responsive">
                <table class="table table-dashboard align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Documento</th>
                            <th scope="col">Producto</th>
                            <th scope="col" class="text-end">Cantidad</th>
                            <th scope="col" class="text-end d-none d-md-table-cell">Valor</th>
                            <th scope="col" class="d-none d-lg-table-cell">Precio</th>
                            <th scope="col">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($downloads as $d)
                            <tr>
                                <td class="font-monospace text-nowrap">{{ $d->document_number }}</td>
                                <td>
                                    @if ($d->product)
                                        <a href="{{ route('admin.products.show', $d->product) }}" class="fw-semibold text-decoration-none">{{ $d->product_name }}</a>
                                    @else
                                        <span class="fw-semibold">{{ $d->product_name }}</span>
                                    @endif
                                    <div class="text-body-secondary small"><span class="font-monospace">{{ $d->sku }}</span>@if ($d->variant_label) · {{ $d->variant_label }}@endif</div>
                                </td>
                                <td class="text-end">{{ rtrim(rtrim((string) $d->quantity, '0'), '.') }}</td>
                                <td class="text-end d-none d-md-table-cell">{{ \App\Support\Money::cop($d->total, 'Cotizar') }}</td>
                                <td class="d-none d-lg-table-cell"><span class="badge {{ $d->price_kind->value === 'direct' ? 'text-bg-success' : ($d->price_kind->value === 'reference' ? 'text-bg-info' : 'text-bg-secondary') }}">{{ $d->price_kind->label() }}</span></td>
                                <td class="text-nowrap">{{ $d->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $downloads->links() }}</div>
        @endif
    </div>
@endsection
