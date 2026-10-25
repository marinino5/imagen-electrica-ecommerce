<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // No se puede borrar una categoría o marca que tenga productos.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->constrained()->restrictOnDelete();
            $table->string('sku', 50)->unique(); // Igual al código de Siigo
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description', 500);
            $table->text('description')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->json('specs')->nullable();
            $table->string('unit', 20)->default('unidad'); // App\Enums\ProductUnit
            $table->decimal('price', 12, 2)->nullable(); // Nulo = solo cotización
            $table->decimal('tax_rate', 5, 2)->default(19.00);
            $table->unsignedInteger('stock')->default(0);
            $table->string('sale_mode', 20)->default('direct'); // App\Enums\SaleMode
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'is_featured']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
