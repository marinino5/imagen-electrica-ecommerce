<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReportPreset;
use App\Support\ReportPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Filtro de periodo de Reportes, aplicable desde la URL:
 * ?period=this_month|last_month|this_year|last_year|custom&from=2026-03-01&to=2026-03-31
 * Sin periodo se usa "este mes". Si algo es inválido, se vuelve a la página
 * sin filtros y se muestra el error junto al formulario.
 */
class ReportRequest extends FormRequest
{
    /** Rango personalizado máximo, para no armar consultas enormes. */
    private const MAX_CUSTOM_DAYS = 1830;

    protected $redirectRoute = 'admin.reports.index';

    public function authorize(): bool
    {
        return true; // El acceso (solo admin) lo controla el middleware de la ruta
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $isCustom = $this->query('period') === ReportPreset::Custom->value;

        return [
            'period' => ['nullable', Rule::enum(ReportPreset::class)],
            'from' => [Rule::requiredIf($isCustom), 'nullable', 'date_format:Y-m-d'],
            'to' => [
                Rule::requiredIf($isCustom), 'nullable', 'date_format:Y-m-d', 'after_or_equal:from',
                fn (string $attribute, mixed $value, \Closure $fail) => $this->exceedsMaxRange($value)
                    ? $fail('El rango personalizado no puede superar los 5 años.')
                    : null,
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'period.enum' => 'El periodo elegido no es válido.',
            'from.required' => 'Indica la fecha inicial del rango.',
            'from.date_format' => 'La fecha inicial no es válida.',
            'to.required' => 'Indica la fecha final del rango.',
            'to.date_format' => 'La fecha final no es válida.',
            'to.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ];
    }

    public function preset(): ReportPreset
    {
        return $this->enum('period', ReportPreset::class) ?? ReportPreset::ThisMonth;
    }

    public function period(): ReportPeriod
    {
        $custom = $this->preset() === ReportPreset::Custom;

        return ReportPeriod::resolve(
            $this->preset(),
            $custom ? CarbonImmutable::parse($this->validated('from')) : null,
            $custom ? CarbonImmutable::parse($this->validated('to')) : null,
        );
    }

    private function exceedsMaxRange(mixed $to): bool
    {
        $from = $this->query('from');

        if (! is_string($from) || ! is_string($to) || ! strtotime($from) || ! strtotime($to)) {
            return false;
        }

        return CarbonImmutable::parse($from)->diffInDays(CarbonImmutable::parse($to)) > self::MAX_CUSTOM_DAYS;
    }
}
