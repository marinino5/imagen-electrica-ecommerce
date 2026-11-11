<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Actualiza muchas filas de una tabla con pocas consultas (UPDATE … CASE), para migraciones y cargas de datos.
 *
 * Contra una base remota cada consulta cuesta unos 100 ms: 450 UPDATE sueltos son casi un minuto; en lotes de 100 son 5 consultas.
 * Funciona igual en MySQL y SQLite. Los valores van con parámetros (nunca concatenados).
 */
class BulkUpdate
{
    /**
     * @param  array<int|string, array<string, mixed>>  $rows  llave de la fila => [columna => valor nuevo]. Todas las filas deben traer las mismas columnas.
     * @return int filas enviadas
     */
    public static function run(string $table, string $key, array $rows, int $chunk = 100): int
    {
        if ($rows === []) {
            return 0;
        }

        $columns = array_keys(reset($rows));
        $now = now();

        foreach (array_chunk($rows, $chunk, true) as $part) {
            $sets = [];
            $bindings = [];

            foreach ($columns as $column) {
                $case = "`{$column}` = CASE `{$key}`";
                foreach ($part as $id => $values) {
                    $case .= ' WHEN ? THEN ?';
                    $bindings[] = (string) $id; // texto: un SKU numérico no debe compararse como número (47040 = '47040A' en MySQL)
                    $bindings[] = $values[$column];
                }
                $sets[] = $case." ELSE `{$column}` END";
            }

            $bindings[] = $now;
            foreach (array_keys($part) as $id) {
                $bindings[] = (string) $id;
            }

            DB::update(
                "UPDATE `{$table}` SET ".implode(', ', $sets).', `updated_at` = ? WHERE `'.$key.'` IN ('.implode(',', array_fill(0, count($part), '?')).')',
                $bindings,
            );
        }

        return count($rows);
    }
}
