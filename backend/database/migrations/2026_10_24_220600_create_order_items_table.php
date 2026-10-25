<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivote orders–products con datos propios de cada línea.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            // Las líneas pertenecen al pedido: si se borra el pedido, se borran.
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Si el producto se elimina definitivamente, la línea queda con su copia.
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // Copia del producto al momento de la solicitud
            $table->string('sku', 50);
            $table->string('product_name');
            $table->string('unit', 20);

            $table->decimal('quantity', 10, 2); // Decimal: el cable se vende por metro
            $table->decimal('unit_price', 12, 2)->nullable(); // Nulo en cotizaciones sin valorar
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('line_total', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
