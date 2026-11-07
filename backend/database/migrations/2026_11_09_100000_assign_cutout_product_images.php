<?php

use Database\Seeders\LineImageSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Migración de datos: pasa las líneas al formato único de imagen de producto (WebP recortado sin fondo, 1000 × 1000,
 * en /img/catalogo/) y copia esa foto a cada referencia de la línea. Reemplaza solo las rutas antiguas del proyecto
 * (/img/productos/… y /img/familias/…) y llena vacíos: una URL cambiada por el equipo no se pisa. Idempotente.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new LineImageSeeder(replaceLegacy: true))->run();
    }

    public function down(): void
    {
        // Datos asignados: no se revierten
    }
};
