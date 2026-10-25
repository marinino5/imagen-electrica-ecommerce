@props(['name', 'label', 'help' => null, 'required' => false])

{{-- Campo de formulario: etiqueta, contenido (slot), ayuda y error de validación --}}
<div {{ $attributes->class('mb-3') }}>
    <label for="{{ $name }}" class="form-label fw-semibold">
        {{ $label }}@if ($required)<span class="text-danger" aria-hidden="true"> *</span>@endif
    </label>
    {{ $slot }}
    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
