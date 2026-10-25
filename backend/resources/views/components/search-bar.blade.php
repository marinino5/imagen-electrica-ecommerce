@props(['action', 'value' => '', 'placeholder' => 'Buscar…'])

{{-- Búsqueda de un listado. Los filtros extra (slot) van en la misma consulta GET. --}}
<form method="GET" action="{{ $action }}" role="search" class="row g-2 align-items-end">
    <div class="col-12 col-md">
        <label for="list-search" class="visually-hidden">Buscar</label>
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search" aria-hidden="true"></i></span>
            <input type="search" id="list-search" name="q" value="{{ $value }}" class="form-control"
                placeholder="{{ $placeholder }}" maxlength="100" autocomplete="off">
        </div>
    </div>
    {{ $slot }}
    <div class="col-auto d-flex gap-2">
        <button type="submit" class="btn btn-primary">Buscar</button>
        @if (request()->query())
            <a href="{{ $action }}" class="btn btn-outline-secondary">Limpiar</a>
        @endif
    </div>
</form>
