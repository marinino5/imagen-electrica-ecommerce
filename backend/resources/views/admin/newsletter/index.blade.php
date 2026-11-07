@extends('layouts.admin')

@section('title', 'Suscriptores')

@section('breadcrumb')
    <li class="breadcrumb-item">General</li>
    <li class="breadcrumb-item active" aria-current="page">Suscriptores</li>
@endsection

@section('content')
    <div class="page-header">
        <h1>Suscriptores del boletín</h1>
        <p>Personas que se suscribieron desde el pie de la tienda y autorizaron el tratamiento de sus datos.</p>
    </div>

    <div class="card">
        <div class="card-body border-bottom">
            <x-search-bar :action="route('admin.newsletter.index')" :value="$search" placeholder="Buscar por correo" />
        </div>

        @if ($subscriptions->isEmpty())
            <x-empty-state icon="envelope-paper" title="Aún no hay suscriptores" message="Cuando alguien se suscriba desde el pie de la tienda, aparecerá aquí." />
        @else
            <div class="table-responsive">
                <table class="table table-dashboard align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Correo</th>
                            <th scope="col">Autorización</th>
                            <th scope="col">Fecha</th>
                            <th scope="col" class="d-none d-md-table-cell">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subscriptions as $subscription)
                            <tr>
                                <td class="fw-semibold">{{ $subscription->email }}</td>
                                <td><span class="status-badge status-paid">Ley 1581 aceptada</span></td>
                                <td class="text-nowrap">{{ $subscription->consented_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                                <td class="d-none d-md-table-cell text-body-secondary">{{ $subscription->ip }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $subscriptions->links() }}</div>
        @endif
    </div>
@endsection
