@extends('layouts.admin')

@section('title', 'Cotizaciones sin cuenta')

@section('breadcrumb')
    <li class="breadcrumb-item">General</li>
    <li class="breadcrumb-item active" aria-current="page">Cotizaciones sin cuenta</li>
@endsection

@section('content')
    <div class="page-header">
        <h1>Cotizaciones sin cuenta</h1>
        <p>Solicitudes que llegan desde la tienda sin crear cuenta. Contacta a la persona por WhatsApp o correo.</p>
    </div>

    <div class="card">
        <div class="card-body border-bottom d-flex flex-wrap gap-2">
            <a href="{{ route('admin.quote-requests.index') }}" @class(['btn btn-sm', 'btn-primary' => ! $status, 'btn-outline-primary' => $status])>Todas</a>
            @foreach (\App\Enums\QuoteRequestStatus::cases() as $case)
                <a href="{{ route('admin.quote-requests.index', ['status' => $case->value]) }}" @class(['btn btn-sm', 'btn-primary' => $status === $case->value, 'btn-outline-primary' => $status !== $case->value])>{{ $case->label() }}</a>
            @endforeach
        </div>

        @if ($requests->isEmpty())
            <x-empty-state icon="chat-left-text" title="Aún no hay solicitudes" message="Cuando alguien envíe una cotización desde la tienda sin cuenta, aparecerá aquí." />
        @else
            <div class="table-responsive">
                <table class="table table-dashboard align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Fecha</th>
                            <th scope="col">Contacto</th>
                            <th scope="col" class="d-none d-md-table-cell">Ciudad</th>
                            <th scope="col" class="text-end">Renglones</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $request)
                            <tr>
                                <td class="text-nowrap">{{ $request->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $request->name }}</span>
                                    @if ($request->company)<small class="d-block text-body-secondary">{{ $request->company }}</small>@endif
                                </td>
                                <td class="d-none d-md-table-cell">{{ $request->city }}</td>
                                <td class="text-end">{{ count($request->items) }}</td>
                                <td><span @class(['status-badge', 'status-new' => $request->status->value === 'new', 'status-paid' => $request->status->value === 'contacted', 'status-cancelled' => $request->status->value === 'closed'])>{{ $request->status->label() }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.quote-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye" aria-hidden="true"></i><span class="visually-hidden">Ver solicitud de {{ $request->name }}</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $requests->links() }}</div>
        @endif
    </div>
@endsection
