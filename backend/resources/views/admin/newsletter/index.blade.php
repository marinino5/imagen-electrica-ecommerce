@extends('layouts.admin')

@section('title', 'Suscriptores')

@section('breadcrumb')
    <li class="breadcrumb-item">Clientes</li>
    <li class="breadcrumb-item active" aria-current="page">Suscriptores</li>
@endsection

@section('content')
    <div class="page-header">
        <h1>Clientes</h1>
        <p>Empresas y personas con cuenta, y quienes se suscribieron al boletín desde el pie de la tienda.</p>
    </div>

    @include('admin.partials.tabs', ['group' => 'clientes'])

    <div class="row g-3 mb-3">
        @foreach ([['Suscriptores', $counts['total']], ['Activos', $counts['active']], ['Dados de baja', $counts['unsubscribed']]] as [$label, $value])
            <div class="col-4"><div class="card"><div class="card-body py-3"><div class="stat-value fs-2">{{ number_format($value, 0, ',', '.') }}</div><div class="stat-label">{{ $label }}</div></div></div></div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body border-bottom">
            <x-search-bar :action="route('admin.newsletter.index')" :value="$search" placeholder="Buscar por correo">
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="estado" class="visually-hidden">Estado</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="active" @selected($status === 'active')>Activos</option>
                        <option value="unsubscribed" @selected($status === 'unsubscribed')>Dados de baja</option>
                    </select>
                </div>
                <div class="col-auto ms-md-auto">
                    <a class="btn btn-outline-primary" href="{{ route('admin.newsletter.export', array_filter(['q' => $search, 'estado' => $status])) }}">
                        <i class="bi bi-download me-1" aria-hidden="true"></i>Exportar CSV
                    </a>
                </div>
            </x-search-bar>
        </div>

        @if ($subscriptions->isEmpty())
            <x-empty-state icon="envelope-paper" title="Aún no hay suscriptores" message="Cuando alguien se suscriba desde el pie de la tienda, aparecerá aquí con la fecha y la versión del texto que aceptó." />
        @else
            <div class="table-responsive">
                <table class="table table-dashboard align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Correo</th>
                            <th scope="col">Consentimiento de datos</th>
                            <th scope="col" class="d-none d-md-table-cell">Estado</th>
                            <th scope="col" class="d-none d-lg-table-cell">IP</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subscriptions as $s)
                            <tr>
                                <td class="fw-semibold">{{ $s->email }}</td>
                                <td>
                                    <span class="status-badge status-paid">Ley 1581 aceptada</span>
                                    <div class="text-body-secondary small">{{ $s->consented_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }} · texto {{ $s->consent_version }}</div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    @if ($s->unsubscribed_at)
                                        <span class="status-badge status-cancelled">Baja</span>
                                        <div class="text-body-secondary small">{{ $s->unsubscribed_at->timezone(config('app.timezone'))->format('d/m/Y') }}</div>
                                    @else
                                        <span class="status-badge status-paid">Activo</span>
                                    @endif
                                </td>
                                <td class="d-none d-lg-table-cell text-body-secondary">{{ $s->ip }}</td>
                                <td class="text-end">
                                    @unless ($s->unsubscribed_at)
                                        <form method="POST" action="{{ route('admin.newsletter.unsubscribe', $s) }}" class="d-inline" data-confirm="¿Dar de baja a {{ $s->email }}? Ya no recibirá correos.">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Dar de baja</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between gap-2 align-items-center">
                <small class="text-body-secondary">Texto de consentimiento vigente: {{ $consentVersion }}. Aquí no se envía nada; ver docs/newsletter.md.</small>
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
@endsection
