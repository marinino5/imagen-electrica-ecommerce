<?php

use Database\Seeders\LineImageSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * «Batería Magna 12 V 5 Ah» era la única línea visible sin foto: recibe una imagen ilustrativa (batería de 12 V recortada).
 * LineImageSeeder solo llena fotos VACÍAS (nunca pisa una que el equipo haya puesto) y es idempotente.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new LineImageSeeder)->run();
    }

    public function down(): void
    {
        // La foto no se quita
    }
};
