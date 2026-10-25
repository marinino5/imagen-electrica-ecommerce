<?php

namespace App\Support;

class Money
{
    /** Pesos colombianos sin decimales: "$ 1.250.000". Nulo muestra $fallback. */
    public static function cop(float|int|string|null $value, string $fallback = '—'): string
    {
        return $value === null ? $fallback : '$ '.number_format((float) $value, 0, ',', '.');
    }
}
