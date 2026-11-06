<?php

use App\Enums\SaleMode;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

/**
 * Correcciones de datos del catálogo ya publicado (las instalaciones nuevas las traen en catalogo.json):
 *
 *  1. "Accesorios de maniobra" mostraba "63–63 A": un rango con los dos extremos iguales pasa a un solo valor.
 *  2. Referencias gemelas (misma etiqueta, distinta tensión) se diferencian con la tensión: "5.6-8 A · 220 V".
 *  3. La familia 08 "Medición y testeo" existía sin fichas y por eso no salía en /families: se crea la línea
 *     "Instrumentos de medición Fluke" (bajo pedido, se cotiza) para que la familia sea real.
 *
 * Solo actúa si el catálogo por líneas ya está cargado (si no, lo siembra CatalogSeeder con estos mismos datos)
 * y solo toca lo que todavía tiene el valor anterior.
 */
return new class extends Migration
{
    private const TWIN_LABELS = [
        '10046368' => ['5.6-8 A', '5.6-8 A · 220 V'],
        '15570522' => ['5.6-8 A', '5.6-8 A · 110 V'],
        '14870194' => ['2 HP', '2 HP · 110 V'],
        '14870191' => ['2 HP', '2 HP · 220 V'],
        '15575728' => ['10 HP', '10 HP · 220 V'],
        '15577022' => ['10 HP', '10 HP · 440 V'],
    ];

    public function up(): void
    {
        if (! ProductLine::query()->exists()) {
            return;
        }

        // 1. Rango con extremos iguales
        $line = ProductLine::where('slug', 'accesorios-de-maniobra')->first();
        if ($line && in_array('63–63 A', $line->spec_chips ?? [], true)) {
            $line->update(['spec_chips' => array_map(fn ($c) => $c === '63–63 A' ? '63 A' : $c, $line->spec_chips)]);
        }

        // 2. Referencias gemelas
        foreach (self::TWIN_LABELS as $sku => [$old, $new]) {
            Product::query()->where('sku', $sku)->where('variant_label', $old)->update(['variant_label' => $new]);
        }

        // 3. Familia 08 con su ficha
        $family = Category::firstOrCreate(
            ['slug' => 'medicion-y-testeo'],
            ['name' => 'Medición y testeo', 'sort_order' => 107, 'is_active' => true],
        );
        $category = Category::firstOrCreate(
            ['slug' => Str::slug('Instrumentos de medición')],
            ['parent_id' => $family->id, 'name' => 'Instrumentos de medición', 'sort_order' => 100, 'is_active' => true],
        );
        $brand = Brand::firstOrCreate(['slug' => 'fluke'], ['name' => 'FLUKE']);

        ProductLine::firstOrCreate(['slug' => 'instrumentos-de-medicion-fluke'], [
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'brand_label' => 'Fluke',
            'name' => 'Instrumentos de medición Fluke',
            'image_key' => 'medicion',
            'spec_chips' => [],
            'sale_mode' => SaleMode::OnRequest,
            'is_featured' => false,
            'sort_order' => (int) ProductLine::max('sort_order') + 1,
        ]);
    }

    public function down(): void
    {
        // Datos corregidos: no se revierten
    }
};
