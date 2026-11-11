<?php

namespace App\Support;

/** NIT colombiano: dígito de verificación (módulo 11 con los pesos de la DIAN). */
final class Nit
{
    private const WEIGHTS = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];

    /** Deja solo los dígitos: «900.123.456-7» → «9001234567». */
    public static function digits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    /** Dígito de verificación del número base (sin DV). Ejemplos reales: 890903938 → 8 (Bancolombia), 800197268 → 4 (DIAN), 899999068 → 1 (Ecopetrol). */
    public static function checkDigit(string $number): int
    {
        $digits = array_reverse(str_split(self::digits($number)));
        $sum = 0;

        foreach ($digits as $i => $digit) {
            $sum += (int) $digit * (self::WEIGHTS[$i] ?? 0);
        }

        $remainder = $sum % 11;

        return $remainder > 1 ? 11 - $remainder : $remainder;
    }

    public static function isValid(string $number, string|int|null $dv): bool
    {
        $digits = self::digits($number);

        return $digits !== '' && strlen($digits) <= 15 && $dv !== null && $dv !== '' && (string) $dv === (string) self::checkDigit($digits);
    }
}
