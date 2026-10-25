@props(['action', 'label', 'confirm', 'icon' => 'trash', 'title' => 'Eliminar'])

{{-- Botón de eliminar con confirmación (data-confirm lo atiende resources/js/panel/confirm.js) --}}
<form method="POST" action="{{ $action }}" class="d-inline" data-confirm="{{ $confirm }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ $title }}">
        <i class="bi bi-{{ $icon }}" aria-hidden="true"></i><span class="visually-hidden">{{ $title }} {{ $label }}</span>
    </button>
</form>
