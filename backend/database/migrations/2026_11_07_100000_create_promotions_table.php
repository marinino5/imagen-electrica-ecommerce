<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Promociones del momento (sección del Inicio de la tienda).
 *
 * Una promoción apunta a una línea (la ficha) y, opcionalmente, a una referencia (SKU) de esa línea.
 * Lleva un porcentaje de descuento o un precio especial (uno de los dos) y una vigencia en fechas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('description', 255)->nullable();
            $table->foreignId('product_line_id')->constrained('product_lines')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->decimal('special_price', 12, 2)->nullable();
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
