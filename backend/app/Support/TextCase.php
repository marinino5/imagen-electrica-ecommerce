<?php

namespace App\Support;

/**
 * Mayúsculas de nombres y ciudades: «maria hernandez» → «María Hernández», «BUCARAMANGA» → «Bucaramanga».
 * Solo toca lo que viene TODO en minúscula o TODO en mayúscula: si la persona ya lo escribió con mayúsculas mezcladas (o es una sigla como
 * «ABC Ltda.»), se respeta. Las tildes se restituyen solo para los nombres y apellidos más comunes (no se adivinan las demás).
 */
final class TextCase
{
    private const PARTICLES = ['de', 'del', 'la', 'las', 'los', 'el', 'y', 'e', 'da', 'di', 'van', 'von'];

    /** Palabra sin tilde (en minúscula) → con tilde. */
    private const ACCENTS = [
        'maria' => 'María', 'jose' => 'José', 'jesus' => 'Jesús', 'andres' => 'Andrés', 'ramon' => 'Ramón', 'ines' => 'Inés', 'angel' => 'Ángel',
        'hernandez' => 'Hernández', 'gomez' => 'Gómez', 'perez' => 'Pérez', 'rodriguez' => 'Rodríguez', 'martinez' => 'Martínez', 'lopez' => 'López',
        'garcia' => 'García', 'gonzalez' => 'González', 'fernandez' => 'Fernández', 'ramirez' => 'Ramírez', 'sanchez' => 'Sánchez', 'diaz' => 'Díaz',
        'jimenez' => 'Jiménez', 'alvarez' => 'Álvarez', 'nino' => 'Niño', 'munoz' => 'Muñoz', 'nunez' => 'Núñez', 'suarez' => 'Suárez',
        'velasquez' => 'Velásquez', 'vasquez' => 'Vásquez', 'marquez' => 'Márquez', 'benitez' => 'Benítez', 'gutierrez' => 'Gutiérrez', 'mendez' => 'Méndez',
        'ordonez' => 'Ordóñez', 'castano' => 'Castaño', 'pena' => 'Peña', 'ibanez' => 'Ibáñez',
    ];

    /** Nombre de persona. */
    public static function person(?string $value): ?string
    {
        return self::apply($value, true);
    }

    /** Ciudad, barrio o dirección corta: pone mayúsculas pero no inventa tildes. */
    public static function place(?string $value): ?string
    {
        return self::apply($value, false);
    }

    /** Razón social: solo si viene toda en minúscula (en mayúsculas es una forma habitual de escribirla). */
    public static function company(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value !== null && $value !== '' && $value === mb_strtolower($value) ? self::apply($value, false) : $value;
    }

    private static function apply(?string $value, bool $accents): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

        if ($value === '' || ($value !== mb_strtolower($value) && $value !== mb_strtoupper($value))) {
            return $value;
        }

        $words = explode(' ', mb_strtolower($value));

        return implode(' ', array_map(function (string $word, int $i) use ($accents) {
            if ($i > 0 && in_array($word, self::PARTICLES, true)) {
                return $word;
            }
            if ($accents && isset(self::ACCENTS[$word])) {
                return self::ACCENTS[$word];
            }

            // «o'brien», «ana-maria»: cada tramo con mayúscula inicial
            return preg_replace_callback('/(^|[\'\-.])(\p{L})/u', fn (array $m) => $m[1].mb_strtoupper($m[2]), $word) ?? $word;
        }, $words, array_keys($words)));
    }
}
