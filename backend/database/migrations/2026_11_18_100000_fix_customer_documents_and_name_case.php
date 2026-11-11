<?php

use App\Enums\IdType;
use App\Support\Nit;
use App\Support\TextCase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Datos de clientes anteriores a los campos de Siigo:
 *
 * 1. Documento: antes había un solo campo «nit» para todo y el panel decía «NIT» incluso con una cédula. Se marca el tipo real
 *    (`id_type`): persona natural → 13 (cédula, sin cambiar el número); empresa → 31 (NIT) con su dígito de verificación calculado.
 *    Lo que ya tiene `id_type` no se toca.
 * 2. Mayúsculas: nombres de personas y ciudades escritos todo en minúscula o todo en mayúscula («maria hernandez», «BUCARAMANGA»)
 *    pasan a «María Hernández», «Bucaramanga». Lo que ya está bien escrito no se toca.
 *
 * Idempotente: se puede correr dos veces sin cambiar nada la segunda vez.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('customers')->whereNull('id_type')->whereNotNull('nit')->orderBy('id')->each(function (object $c) {
            if ($c->type === 'natural') {
                DB::table('customers')->where('id', $c->id)->update(['id_type' => IdType::CC->value]);

                return;
            }

            // Empresa: «900123456-7» ya trae el dígito; «900123456» se completa
            $base = Nit::digits(explode('-', (string) $c->nit)[0]);
            if ($base === '') {
                return;
            }
            DB::table('customers')->where('id', $c->id)->update([
                'id_type' => IdType::NIT->value,
                'check_digit' => str_contains((string) $c->nit, '-') ? Nit::digits(explode('-', (string) $c->nit)[1] ?? '') : (string) Nit::checkDigit($base),
            ]);
        });

        $this->recase('users', ['name' => 'person']);
        $this->recase('customers', ['contact_name' => 'person', 'first_name' => 'person', 'last_name' => 'person', 'city' => 'place', 'company_name' => 'company']);
        // Los pedidos guardan copia del cliente: solo se arregla cómo se ve el nombre y la ciudad, no los datos fiscales
        $this->recase('orders', ['contact_name' => 'person', 'city' => 'place']);
    }

    /** @param array<string, 'person'|'place'|'company'> $columns */
    private function recase(string $table, array $columns): void
    {
        DB::table($table)->orderBy('id')->each(function (object $row) use ($table, $columns) {
            $changes = [];
            foreach ($columns as $column => $kind) {
                $fixed = TextCase::{$kind}($row->{$column});
                if ($fixed !== null && $fixed !== $row->{$column} && $fixed !== '') {
                    $changes[$column] = $fixed;
                }
            }
            if ($changes) {
                DB::table($table)->where('id', $row->id)->update($changes);
            }
        });
    }

    public function down(): void
    {
        // Datos corregidos: no se revierten
    }
};
