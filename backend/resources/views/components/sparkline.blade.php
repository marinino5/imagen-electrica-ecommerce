@props(['values' => [], 'label' => 'Tendencia del período'])
{{-- Minigráfica de un indicador: línea y área de la serie del período (SVG, sin librerías). Va con aria-label para lectores de pantalla. --}}
@php
    $values = array_values(array_map('floatval', $values));
    $n = count($values);
    $w = 120; $h = 36; $pad = 3;
    $max = $n ? max($values) : 0;
    $min = $n ? min($values) : 0;
    $span = max($max - $min, 1e-9);
    $points = [];
    foreach ($values as $i => $v) {
        $x = $n > 1 ? $pad + $i * (($w - 2 * $pad) / ($n - 1)) : $w / 2;
        $y = $max == $min ? $h - $pad - 2 : $h - $pad - (($v - $min) / $span) * ($h - 2 * $pad);
        $points[] = round($x, 1).','.round($y, 1);
    }
    $line = implode(' ', $points);
    $area = $n > 1 ? $pad.','.($h - $pad).' '.$line.' '.($w - $pad).','.($h - $pad) : '';
    $flat = $n === 0 || $max == $min;
@endphp
@if ($n > 1)
    <svg class="sparkline" viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none" role="img" aria-label="{{ $label }}" focusable="false">
        @unless ($flat)<polygon points="{{ $area }}" class="sparkline-area" />@endunless
        <polyline points="{{ $line }}" class="sparkline-line" fill="none" />
        @if (! $flat)
            @php([$lx, $ly] = explode(',', end($points)))
            <circle cx="{{ $lx }}" cy="{{ $ly }}" r="2.4" class="sparkline-dot" />
        @endif
    </svg>
@endif
