@php
    use App\Enums\ReportPreset;
    use Carbon\CarbonImmutable;

    // El período vigente: el de la URL o el que se recordó en la sesión (RememberPanelPeriod ya lo mezcló en la URL)
    $current = ReportPreset::tryFrom((string) request()->query('period')) ?? ReportPreset::Last30;
    $custom = $current === ReportPreset::Custom && request()->query('from') && request()->query('to');
    $label = $custom
        ? CarbonImmutable::parse(request()->query('from'))->format('d/m/Y').' – '.CarbonImmutable::parse(request()->query('to'))->format('d/m/Y')
        : $current->label();
    $keep = request()->except(['period', 'from', 'to', 'page']);
@endphp
{{-- Filtro de período global: se aplica a Inicio y a Análisis y se recuerda en la sesión --}}
<div class="dropdown period-filter">
    <button type="button" class="period-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Período: {{ $label }}">
        <i class="bi bi-calendar3" aria-hidden="true"></i>
        <span class="period-btn-text"><small>Período</small><strong>{{ $label }}</strong></span>
        <i class="bi bi-chevron-down small" aria-hidden="true"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-end period-menu p-2">
        @foreach (ReportPreset::cases() as $preset)
            @continue($preset === ReportPreset::Custom)
            <a @class(['dropdown-item', 'active' => $current === $preset]) href="{{ request()->url().'?'.http_build_query($keep + ['period' => $preset->value]) }}">{{ $preset->label() }}</a>
        @endforeach
        <hr class="dropdown-divider">
        <form method="GET" action="{{ request()->url() }}" class="px-2 pb-2">
            @foreach ($keep as $name => $value)
                @if (is_scalar($value))<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif
            @endforeach
            <input type="hidden" name="period" value="custom">
            <div class="small fw-semibold mb-1">Rango personalizado</div>
            <label class="visually-hidden" for="pf-from">Desde</label>
            <input type="date" id="pf-from" name="from" class="form-control form-control-sm mb-1" value="{{ request()->query('from') }}" required>
            <label class="visually-hidden" for="pf-to">Hasta</label>
            <input type="date" id="pf-to" name="to" class="form-control form-control-sm mb-2" value="{{ request()->query('to') }}" required>
            <button type="submit" class="btn btn-sm btn-primary w-100">Aplicar</button>
        </form>
    </div>
</div>
