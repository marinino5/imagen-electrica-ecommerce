@props(['icon' => 'inbox', 'title', 'message' => null])

{{-- Estado vacío: ícono, título y mensaje opcional --}}
<div {{ $attributes->class('empty-state') }}>
    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-{{ $icon }}"></i></span>
    <p class="empty-state-title">{{ $title }}</p>
    @if ($message)
        <p class="small mb-0">{{ $message }}</p>
    @endif
    {{ $slot }}
</div>
