<?php

use App\Enums\SaleMode;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

/**
 * Migración de datos: la línea «Motores de otras marcas» mezclaba cuatro fabricantes y por eso mostraba una foto de WEG.
 * Se divide en una línea por marca (ABB, Nidec US Motors, Nord, Match Drive), cada una con su foto oficial o, si no la
 * hay (Match Drive), con el panel de marca. La única referencia WEG que quedaba ahí conserva la línea, con nombre de marca.
 *
 * Las instalaciones nuevas ya traen esto en catalogo.json; aquí solo se mueve lo que ya existe en la base publicada.
 * Idempotente: sin la línea antigua no hace nada, y cada paso usa firstOrCreate o solo toca lo que aún tiene el valor anterior.
 */
return new class extends Migration
{
    private const OLD_SLUG = 'motores-de-otras-marcas';

    private const OLD_IMAGE = '/img/catalogo/weg-w22-lateral.webp';

    /** marca (slug) → [nombre de la línea nueva, slug de la categoría, foto o null, chips] */
    private const BRANDS = [
        'abb' => ['Motores ABB', 'atmosferas-explosivas', '/img/catalogo/abb-motor.webp', ['125 HP', '1800 RPM']],
        'nidec' => ['Motores Nidec US Motors', 'motores-trifasicos', '/img/catalogo/nidec-motor.webp', ['20 HP', '1800 RPM']],
        'nord' => ['Motores Nord', 'motores-trifasicos', '/img/catalogo/nord-motor.webp', ['1–7,5 HP', '1800 RPM']],
        'match-drive' => ['Motores Match Drive', 'motores-monofasicos', null, ['1–2 HP', '1800 RPM']],
    ];

    public function up(): void
    {
        $old = ProductLine::where('slug', self::OLD_SLUG)->first();
        if (! $old) {
            return;
        }

        $order = (int) $old->sort_order;

        foreach (self::BRANDS as $brandSlug => [$name, $categorySlug, $image, $chips]) {
            $brand = Brand::where('slug', $brandSlug)->first();
            $category = Category::where('slug', $categorySlug)->first();
            if (! $brand || ! $category) {
                continue;
            }

            $line = ProductLine::firstOrCreate(['slug' => Str::slug($name)], [
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'brand_label' => $brand->name,
                'name' => $name,
                'image_key' => 'motores',
                'image_url' => $image,
                'spec_chips' => $chips,
                'sale_mode' => SaleMode::Quote,
                'is_featured' => false,
                'sort_order' => $order,
            ]);

            // Se mueven las referencias de esa marca; su foto deja de ser la de WEG
            $moved = Product::withTrashed()->where('line_id', $old->id)->where('brand_id', $brand->id)->get();
            foreach ($moved as $product) {
                $product->line_id = $line->id;
                if ($product->image_url === self::OLD_IMAGE || $product->image_url === null) {
                    $product->image_url = $image;
                }
                $product->save();
            }

            $this->refreshMode($line);
        }

        // La referencia WEG que queda conserva la línea, con nombre de marca
        $old->update([
            'name' => 'Motor trifásico WEG de bobinado',
            'slug' => 'motor-trifasico-weg-de-bobinado',
            'brand_label' => 'WEG',
            'spec_chips' => ['1 HP', '1800 RPM'],
        ]);
        $this->refreshMode($old);
    }

    public function down(): void
    {
        // Datos reorganizados: no se revierten
    }

    /** El modo de la línea sigue a sus referencias: alguna directa → directa; solo cotizar → cotizar. */
    private function refreshMode(ProductLine $line): void
    {
        $modes = Product::where('line_id', $line->id)->pluck('sale_mode')->map(fn ($m) => $m instanceof SaleMode ? $m->value : $m);
        if ($modes->isEmpty()) {
            return;
        }
        $line->update(['sale_mode' => $modes->contains('direct') ? SaleMode::Direct : SaleMode::Quote]);
    }
};
