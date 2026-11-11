<?php

use App\Models\ProductLine;
use App\Services\CatalogLayoutService;
use Illuminate\Database\Migrations\Migration;

/**
 * El panel muestra las MISMAS familias y categorías que la tienda: las de la primera versión (Motorreductores › Sinfín corona, Ventilación ›
 * Extractores…) se migran a las de la tienda (ver CatalogLayoutService::migrateLegacyCategories). No toca precios ni stock. Idempotente.
 * En una instalación nueva (sin líneas) no hace nada: lo hace DatabaseSeeder después de sembrar el catálogo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (ProductLine::query()->exists()) {
            app(CatalogLayoutService::class)->migrateLegacyCategories();
        }
    }

    public function down(): void
    {
        // Datos reorganizados: no se revierten
    }
};
