@props(['document' => null])
{{-- Documento del cliente: «CC 1097490864» o «NIT 900123456-7», con la etiqueta Persona natural / Empresa --}}
@if ($document)
    <span class="doc-chip">
        <span class="font-monospace fw-semibold">{{ $document['full'] }}</span>
        <span @class(['doc-kind', 'is-company' => $document['is_company']])>{{ $document['kind'] }}</span>
    </span>
@else
    <span class="text-body-secondary">—</span>
@endif
