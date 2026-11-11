<?php

use App\Enums\SaleMode;
use App\Models\Product;
use App\Models\ProductLine;
use App\Services\CatalogPriceService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 1. Agrega products.price_source (nulo = precio confirmado; «referencia» = valor de mercado por confirmar).
 * 2. Carga los precios de referencia de las referencias nuevas de la Fase 1B en una base que ya tiene el catálogo
 *    (CatalogPriceService::loadReferencePrices). En una base nueva no hay productos todavía: CatalogSeeder los carga al final.
 * Idempotente: nunca pisa un precio real, simulado ni ya cargado.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'price_source')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('price_source', 20)->nullable()->after('price_is_simulated');
                $table->index('price_source');
            });
        }

        app(CatalogPriceService::class)->loadReferencePrices();
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'price_source')) {
            // Los precios de referencia no sobreviven sin su marca: las referencias vuelven a cotización
            $lineIds = Product::where('price_source', 'referencia')->whereNotNull('line_id')->pluck('line_id')->unique();
            Product::where('price_source', 'referencia')->update(['price' => null, 'sale_mode' => SaleMode::Quote]);
            foreach ($lineIds as $lineId) {
                if (! Product::where('line_id', $lineId)->where('sale_mode', SaleMode::Direct)->exists()) {
                    ProductLine::whereKey($lineId)->update(['sale_mode' => SaleMode::Quote]);
                }
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['price_source']);
                $table->dropColumn('price_source');
            });
        }
    }
};
