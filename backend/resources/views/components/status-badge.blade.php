@props(['status'])

{{-- Insignia de estado; los colores de cada estado están en _base.scss (.status-*) --}}
<span {{ $attributes->class(['status-badge', 'status-'.$status->value]) }}>{{ $status->label() }}</span>
