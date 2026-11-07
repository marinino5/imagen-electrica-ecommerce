<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migración de datos: cambia tres imágenes provisionales por las oficiales del fabricante (compresor ABAC, cable Centelsa by
 * Nexans con el conductor visible). Solo reemplaza la ruta antigua exacta, en líneas y referencias, así que una foto que el
 * equipo haya cambiado a mano no se pisa. Idempotente: si ya no queda la ruta antigua, no hace nada.
 */
return new class extends Migration
{
    /** @var array<string, string> */
    private const MAP = [
        '/img/catalogo/compresor-ilustracion.svg' => '/img/catalogo/abac-compresor-vertical.webp',
        '/img/catalogo/thhn-centelsa.webp' => '/img/catalogo/centelsa-cable-thhn.webp',
    ];

    public function up(): void
    {
        foreach (self::MAP as $old => $new) {
            DB::table('product_lines')->where('image_url', $old)->update(['image_url' => $new]);
            DB::table('product_lines')->where('image_alt_url', $old)->update(['image_alt_url' => $new]);
            DB::table('products')->where('image_url', $old)->update(['image_url' => $new]);
        }
    }

    public function down(): void
    {
        // Datos asignados: no se revierten
    }
};
