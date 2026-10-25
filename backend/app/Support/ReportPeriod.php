<?php

namespace App\Support;

use App\Enums\ReportPreset;
use Carbon\CarbonImmutable;

/**
 * Rango de fechas de un reporte y el periodo anterior con el que se compara.
 * `start` es inclusivo y `end` exclusivo (el inicio del día siguiente al último
 * día), así una consulta `>= start AND < end` incluye el último día completo.
 *
 * El periodo anterior se compara "en igual medida":
 * - Este mes / este año (en curso): el mismo tramo del mes o año anterior
 *   (del 1 al día 10 de este mes contra del 1 al 10 del mes pasado).
 * - Mes anterior / año anterior: el mes o año completo que lo precede.
 * - Rango personalizado: el rango de igual duración justo antes del elegido.
 */
final class ReportPeriod
{
    private function __construct(
        public readonly ReportPreset $preset,
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $end,
        public readonly CarbonImmutable $previousStart,
        public readonly CarbonImmutable $previousEnd,
    ) {}

    public static function resolve(ReportPreset $preset, ?CarbonImmutable $from = null, ?CarbonImmutable $to = null, ?CarbonImmutable $now = null): self
    {
        $now ??= CarbonImmutable::now();
        $tomorrow = $now->startOfDay()->addDay();

        return match ($preset) {
            ReportPreset::ThisMonth => self::calendar($preset, $now->startOfMonth(), $tomorrow, 'month', complete: false),
            ReportPreset::LastMonth => self::calendar($preset, $now->startOfMonth()->subMonthNoOverflow(), $now->startOfMonth(), 'month', complete: true),
            ReportPreset::ThisYear => self::calendar($preset, $now->startOfYear(), $tomorrow, 'year', complete: false),
            ReportPreset::LastYear => self::calendar($preset, $now->startOfYear()->subYear(), $now->startOfYear(), 'year', complete: true),
            ReportPreset::Custom => self::custom($from ?? $now->startOfMonth(), $to ?? $now),
        };
    }

    /** Etiqueta para el encabezado: "Octubre 2026", "2026" o "01/03/2026 – 15/03/2026". */
    public function label(): string
    {
        $last = $this->end->subDay();

        return match ($this->preset) {
            ReportPreset::ThisMonth, ReportPreset::LastMonth => ucfirst($this->start->locale('es')->isoFormat('MMMM YYYY')),
            ReportPreset::ThisYear, ReportPreset::LastYear => (string) $this->start->year,
            ReportPreset::Custom => $this->start->format('d/m/Y').' – '.$last->format('d/m/Y'),
        };
    }

    /** Etiqueta del periodo anterior, para las tarjetas ("vs. septiembre 2026"). */
    public function previousLabel(): string
    {
        $last = $this->previousEnd->subDay();

        return match ($this->preset) {
            ReportPreset::ThisMonth, ReportPreset::LastMonth => $this->previousStart->locale('es')->isoFormat('MMMM YYYY'),
            ReportPreset::ThisYear, ReportPreset::LastYear => (string) $this->previousStart->year,
            ReportPreset::Custom => $this->previousStart->format('d/m/Y').' – '.$last->format('d/m/Y'),
        };
    }

    /** Fecha del último día incluido (para mostrar en formularios y nombres de archivo). */
    public function lastDay(): CarbonImmutable
    {
        return $this->end->subDay();
    }

    /**
     * Mes o año calendario. Si el periodo está completo, el anterior es el mes
     * o año completo que lo precede. Si está en curso, el anterior dura los
     * mismos días (sin pasarse del inicio del actual).
     */
    private static function calendar(ReportPreset $preset, CarbonImmutable $start, CarbonImmutable $end, string $unit, bool $complete): self
    {
        $previousStart = $unit === 'month' ? $start->subMonthNoOverflow() : $start->subYear();
        $previousEnd = $complete ? $start : $previousStart->addDays((int) $start->diffInDays($end))->min($start);

        return new self($preset, $start, $end, $previousStart, $previousEnd);
    }

    private static function custom(CarbonImmutable $from, CarbonImmutable $to): self
    {
        $start = $from->startOfDay();
        $end = $to->startOfDay()->addDay();
        $days = (int) $start->diffInDays($end);

        return new self(ReportPreset::Custom, $start, $end, $start->subDays($days), $start);
    }
}
