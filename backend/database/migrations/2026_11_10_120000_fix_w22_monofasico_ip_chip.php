<?php

use App\Models\ProductLine;
use Illuminate\Database\Migrations\Migration;

/**
 * Migración de datos: la línea «W22 monofásico» mostraba «IP21», pero 7 de sus 8 referencias son IP55 (el W22 monofásico de
 * WEG es IP55; solo la de 5 HP del inventario trae IP21). El chip pasa a «IP55 / IP21». Fuente en docs/correcciones-especificaciones.md.
 * Solo toca el chip si todavía dice exactamente «IP21»; idempotente.
 */
return new class extends Migration
{
    public function up(): void
    {
        $line = ProductLine::where('slug', 'w22-monofasico')->first();

        if (! $line || ! in_array('IP21', $line->spec_chips ?? [], true)) {
            return;
        }

        $line->spec_chips = array_map(fn ($chip) => $chip === 'IP21' ? 'IP55 / IP21' : $chip, $line->spec_chips);
        $line->save();
    }

    public function down(): void
    {
        // Dato corregido: no se revierte
    }
};
