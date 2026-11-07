<?php

use Database\Seeders\LineImageSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Migración de datos: asigna las imágenes oficiales de database/data/line-images.json a las líneas que aún no
 * tienen foto. Solo llena vacíos, así que no pisa una imagen que el equipo ya haya cambiado.
 * Fuente de cada archivo: docs/creditos-media.md. Lo que no se consiguió: docs/imagenes-pendientes.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new LineImageSeeder)->run();
    }

    public function down(): void
    {
        // Datos asignados: no se revierten
    }
};
