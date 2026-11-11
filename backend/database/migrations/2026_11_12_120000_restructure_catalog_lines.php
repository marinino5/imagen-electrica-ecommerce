<?php

use App\Models\ProductLine;
use App\Services\CatalogLayoutService;
use Illuminate\Database\Migrations\Migration;

/**
 * Revisión producto por producto: cada línea agrupa solo variantes del mismo producto (mismo tipo y marca), con nombre
 * «qué es + marca + serie», etiqueta que distingue cada variante, especificaciones con tipo y serie, y la foto que corresponde.
 * Ejemplo: «Contactores» (mezcla de 7 productos) pasa a «Contactor WEG CWB», «Mini contactor WEG CWC», etc.
 * La estructura sale de database/data/catalogo.json; ver CatalogLayoutService para lo que toca y lo que no (precio, stock, SKU).
 * Las instalaciones nuevas no pasan por aquí: sin líneas no hace nada y las siembra CatalogSeeder con la misma estructura.
 * Idempotente.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! ProductLine::query()->exists()) {
            return;
        }

        app(CatalogLayoutService::class)->sync();
    }

    public function down(): void
    {
        // Datos reorganizados: no se revierten
    }
};
