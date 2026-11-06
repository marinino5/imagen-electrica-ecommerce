<?php

use Database\Seeders\LineImageSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Migración de datos: asigna las fotos del manifiesto database/data/line-images.json a las líneas que no tienen foto.
 * Solo llena vacíos (nunca pisa una foto cambiada). Para asignar fotos nuevas más adelante:
 *   php artisan db:seed --class=LineImageSeeder
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
