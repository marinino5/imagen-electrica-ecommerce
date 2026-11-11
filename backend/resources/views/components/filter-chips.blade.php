@props(['param', 'options', 'label' => 'Filtrar'])
{{-- Filtro por chips: cada chip es un enlace que cambia solo `$param` y conserva el resto de la consulta (búsqueda, otros filtros).
     $options = ['valor' => 'Texto']; el valor vacío es «Todos». --}}
@php($current = (string) request()->query($param, ''))
<div class="chip-row" role="group" aria-label="{{ $label }}">
    @foreach ($options as $value => $text)
        @php($href = request()->url().'?'.http_build_query(array_filter(array_merge(request()->except([$param, 'page']), [$param => $value]), fn ($v) => $v !== '' && $v !== null)))
        <a href="{{ $href }}" @class(['chip', 'is-on' => $current === (string) $value]) @if ($current === (string) $value) aria-current="true" @endif>{{ $text }}</a>
    @endforeach
</div>
