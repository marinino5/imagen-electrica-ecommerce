{{-- Notificaciones de éxito (session "status") y error (session "error") --}}
@php
    $toasts = array_filter([
        'success' => session('status'),
        'error' => session('error'),
    ]);
    $icons = ['success' => 'check-circle-fill', 'error' => 'exclamation-octagon-fill'];
@endphp

<div class="toast-stack toast-container position-fixed top-0 end-0 p-3">
    @foreach ($toasts as $type => $message)
        <div class="toast app-toast app-toast-{{ $type }}" data-autoshow
            role="{{ $type === 'error' ? 'alert' : 'status' }}" aria-live="{{ $type === 'error' ? 'assertive' : 'polite' }}" aria-atomic="true">
            <div class="toast-body d-flex align-items-start gap-2">
                <i class="bi bi-{{ $icons[$type] }} toast-icon" aria-hidden="true"></i>
                <span class="flex-grow-1">{{ $message }}</span>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    @endforeach
</div>
