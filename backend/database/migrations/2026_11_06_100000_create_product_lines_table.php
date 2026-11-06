<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Líneas de producto (la ficha del catálogo) y sus referencias.
 *
 * Familia → categoría → línea → referencias:
 *  - la familia y la categoría ya existen en `categories` (la familia es la categoría sin padre);
 *  - la línea es la ficha que abre el cliente (`product_lines`);
 *  - cada referencia (SKU de Siigo) sigue siendo un `products`, ahora con `line_id`.
 * Así el carrito, los pedidos y el inventario no cambian: la variante es el producto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete(); // marca principal
            $table->string('brand_label', 120)->nullable(); // como se muestra: "Siemens · WEG"
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_key', 60)->nullable(); // imagen de la línea en la tienda (img/categorias)
            $table->json('spec_chips')->nullable(); // ["1–50 HP", "1200 / 1800 / 3600 RPM"]
            $table->string('sale_mode', 20)->default('direct'); // App\Enums\SaleMode: direct, quote, on_request
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('line_id')->nullable()->after('brand_id')->constrained('product_lines')->nullOnDelete();
            $table->string('variant_label', 80)->nullable()->after('name'); // "5 HP · 1800 RPM", "2,8–4 A"
            // Los precios del Excel de inventario son de prueba: el panel los marca así hasta tener los reales
            $table->boolean('price_is_simulated')->default(false)->after('price');
            $table->index('line_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('line_id');
            $table->dropColumn(['variant_label', 'price_is_simulated']);
        });
        Schema::dropIfExists('product_lines');
    }
};
