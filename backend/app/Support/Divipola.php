<?php

namespace App\Support;

/**
 * Departamentos y municipios de Colombia con su código DANE (DIVIPOLA). Fuente: datos abiertos del Gobierno
 * (datos.gov.co, conjunto «Departamentos y municipios de Colombia»), guardada en database/data/divipola.json
 * (33 departamentos y 1.122 municipios). El código del municipio son 5 dígitos: los 2 primeros son los del departamento.
 */
final class Divipola
{
    /** @var list<array{code: string, name: string, cities: list<array{code: string, name: string}>}>|null */
    private static ?array $data = null;

    /** @return list<array{code: string, name: string, cities: list<array{code: string, name: string}>}> */
    public static function all(): array
    {
        return self::$data ??= json_decode((string) file_get_contents(database_path('data/divipola.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    /** @return list<array{code: string, name: string}> */
    public static function departments(): array
    {
        return array_map(fn (array $d) => ['code' => $d['code'], 'name' => $d['name']], self::all());
    }

    /** @return array{code: string, name: string}|null */
    public static function department(string $code): ?array
    {
        foreach (self::all() as $d) {
            if ($d['code'] === $code) {
                return ['code' => $d['code'], 'name' => $d['name']];
            }
        }

        return null;
    }

    /** @return list<array{code: string, name: string}> */
    public static function cities(string $departmentCode): array
    {
        foreach (self::all() as $d) {
            if ($d['code'] === $departmentCode) {
                return $d['cities'];
            }
        }

        return [];
    }

    /** @return array{code: string, name: string}|null el municipio, solo si pertenece a ese departamento */
    public static function city(string $departmentCode, string $cityCode): ?array
    {
        foreach (self::cities($departmentCode) as $c) {
            if ($c['code'] === $cityCode) {
                return $c;
            }
        }

        return null;
    }
}
