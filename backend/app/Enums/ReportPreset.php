<?php

namespace App\Enums;

/** Periodos que ofrece el filtro de la página Reportes. */
enum ReportPreset: string
{
    case ThisMonth = 'this_month';
    case LastMonth = 'last_month';
    case ThisYear = 'this_year';
    case LastYear = 'last_year';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::ThisMonth => 'Este mes',
            self::LastMonth => 'Mes anterior',
            self::ThisYear => 'Este año',
            self::LastYear => 'Año anterior',
            self::Custom => 'Rango personalizado',
        };
    }
}
