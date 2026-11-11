<?php

namespace Tests\Support;

/** Cifras del catálogo que salen de database/data/catalogo.json: así las pruebas no se rompen cada vez que se agregan líneas. */
class CatalogData
{
    /** Referencias que ya estaban publicadas antes de la Fase 1B (catalogo.base.json). */
    public const BASE_REFERENCES = 351;

    private static function json(): array
    {
        return json_decode((string) file_get_contents(database_path('data/catalogo.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    /** Líneas publicadas: las ocultas hasta tener foto oficial (CatalogSeeder::HIDDEN_UNTIL_PHOTO) no cuentan en la API pública. */
    private static function visible(): array
    {
        return array_values(array_filter(self::json()['lines'], fn ($l) => ! in_array($l['name'], \Database\Seeders\CatalogSeeder::HIDDEN_UNTIL_PHOTO, true)));
    }

    /** Líneas que siembra el catálogo (con las ocultas). */
    public static function allLines(): int
    {
        return count(self::json()['lines']);
    }

    /** Referencias que siembra el catálogo (con las de líneas ocultas). */
    public static function allReferences(): int
    {
        return array_sum(array_map(fn ($l) => count($l['variants']), self::json()['lines']));
    }

    public static function lines(): int
    {
        return count(self::visible());
    }

    public static function references(): int
    {
        return array_sum(array_map(fn ($l) => count($l['variants']), self::visible()));
    }

    public static function linesInFamily(string $family): int
    {
        return count(array_filter(self::visible(), fn ($l) => $l['family'] === $family));
    }

    public static function families(): int
    {
        return count(self::json()['families']);
    }

    public static function linesWithoutReferences(): int
    {
        return count(array_filter(self::json()['lines'], fn ($l) => $l['variants'] === []));
    }

    /** Referencias de la Fase 1B: las que traen su foto propia en `image`. @return list<array<string, mixed>> */
    public static function phase1Variants(): array
    {
        return array_values(array_filter(
            array_merge(...array_map(fn ($l) => $l['variants'], self::json()['lines'])),
            fn ($v) => isset($v['image']),
        ));
    }
}
