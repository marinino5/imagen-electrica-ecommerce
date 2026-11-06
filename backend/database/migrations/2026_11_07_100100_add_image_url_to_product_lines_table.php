<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foto propia de cada línea (la que se ve en el catálogo y en la ficha).
 *
 * `image_key` queda como respaldo: es la ilustración genérica de la categoría. `image_url` es la ruta pública
 * de la foto del producto en la tienda (ej. /img/productos/control/weg-contactores.webp); una migración
 * posterior la llena solo donde está vacía.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_lines', function (Blueprint $table) {
            $table->string('image_url', 255)->nullable()->after('image_key');
        });
    }

    public function down(): void
    {
        Schema::table('product_lines', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
    }
};
