<?php

use App\Models\Product;
use App\Models\ProductLine;
use Database\Seeders\CatalogSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Regla de fotos de producto: una línea sin foto oficial de la marca no se publica. Match Drive no tiene foto oficial del motor
 * (no hay sitio ni catálogo del fabricante accesible): la línea y sus referencias quedan ocultas hasta tenerla.
 * Para publicarla de nuevo: poner la foto, quitar el nombre de CatalogSeeder::HIDDEN_UNTIL_PHOTO y activarla en el panel.
 * Idempotente.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (CatalogSeeder::HIDDEN_UNTIL_PHOTO as $name) {
            $line = ProductLine::where('name', $name)->whereNull('image_url')->first();

            if ($line) {
                $line->update(['is_active' => false]);
                Product::where('line_id', $line->id)->update(['is_active' => false]);
            }
        }
    }

    public function down(): void
    {
        // Ocultar es una decisión de publicación: no se revierte
    }
};
