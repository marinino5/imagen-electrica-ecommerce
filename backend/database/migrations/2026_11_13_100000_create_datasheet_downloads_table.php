<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Fichas técnicas con cotización que descargan los visitantes de la tienda (PDF generado por el backend). */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('datasheet_downloads')) {
            return;
        }

        Schema::create('datasheet_downloads', function (Blueprint $table) {
            $table->id();
            $table->string('document_number', 30)->nullable()->unique(); // IE-FT-2026-00123 (se completa con el id)
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            // Copia: si el producto cambia o se elimina, el registro de lo que se entregó no cambia
            $table->string('sku', 60);
            $table->string('product_name');
            $table->string('variant_label', 120)->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('price_kind', 12); // App\Enums\DatasheetPriceKind: direct, reference, quote
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datasheet_downloads');
    }
};
