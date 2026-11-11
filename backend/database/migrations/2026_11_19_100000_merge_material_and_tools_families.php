<?php

use App\Services\CatalogLayoutService;
use Illuminate\Database\Migrations\Migration;

/**
 * Fusiona las familias «Material Eléctrico» y «Herramientas y Repuestos» en una sola, «Material Eléctrico y Repuestos» (quedan 9 familias).
 * Ver CatalogLayoutService::mergeRepuestosFamilies: mueve categorías, líneas y productos, y elimina las familias viejas. Idempotente.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(CatalogLayoutService::class)->mergeRepuestosFamilies();
    }

    public function down(): void
    {
        // La fusión no se revierte
    }
};
