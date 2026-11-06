<?php

namespace Database\Seeders;

use App\Enums\ProductUnit;
use App\Enums\SaleMode;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Catálogo por líneas: familia → categoría → línea (ficha) → referencias (SKU).
 *
 * Los datos salen de database/data/catalogo.json, que se genera con
 * docs/datos/generar_catalogo.py desde el Excel de inventario y el portafolio.
 * Los precios del Excel son de prueba: cada referencia queda con price_is_simulated.
 *
 * Idempotente: busca por slug (familias, categorías, líneas) y por sku (referencias);
 * lo que ya existe no se modifica, así no se pisan precios ni stock cambiados después.
 */
class CatalogSeeder extends Seeder
{
    /** Líneas que se muestran en el carril de destacados del inicio. */
    private const FEATURED = [
        'W22 IEC · carcasa hierro', 'W21 IEC · carcasa aluminio', 'Variador CFW300',
        'Contactores', 'Guardamotores', 'W01 monofásico cerrado',
    ];

    public function run(): void
    {
        $data = json_decode((string) file_get_contents(database_path('data/catalogo.json')), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($data) {
            $families = $this->seedFamilies($data['families']);
            $categories = [];
            $order = 0;

            foreach ($data['lines'] as $row) {
                $family = $families[$row['family']];
                $categories[$row['family'].'|'.$row['category']] ??= $this->category($family, $row['category']);
                $this->seedLine($row, $categories[$row['family'].'|'.$row['category']], ++$order);
            }
        });
    }

    /** @return array<string, Category> familias por nombre */
    private function seedFamilies(array $families): array
    {
        $result = [];

        foreach ($families as $i => $family) {
            $result[$family['name']] = Category::firstOrCreate(
                ['slug' => Str::slug($family['name'])],
                ['name' => $family['name'], 'description' => $family['description'], 'sort_order' => 100 + $i, 'is_active' => true],
            );
        }

        return $result;
    }

    /** La categoría puede existir de antes bajo otra familia: se mueve a la nueva. */
    private function category(Category $family, string $name): Category
    {
        $category = Category::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['parent_id' => $family->id, 'name' => $name, 'sort_order' => 100, 'is_active' => true],
        );

        if ($category->parent_id !== $family->id) {
            $category->update(['parent_id' => $family->id]);
        }

        return $category;
    }

    private function seedLine(array $row, Category $category, int $order): void
    {
        $variants = $row['variants'];
        $mode = match (true) {
            $variants === [] => SaleMode::OnRequest,
            collect($variants)->contains('mode', 'direct') => SaleMode::Direct,
            default => SaleMode::Quote,
        };
        $mainBrand = $row['brand_label'] ? Brand::where('slug', Str::slug(explode(' · ', $row['brand_label'])[0]))->first() : null;

        $line = ProductLine::firstOrCreate(['slug' => Str::slug($row['name'])], [
            'category_id' => $category->id,
            'brand_id' => $mainBrand?->id,
            'brand_label' => $row['brand_label'],
            'name' => $row['name'],
            'image_key' => $row['image'],
            'spec_chips' => $row['specs'],
            'sale_mode' => $mode,
            'is_featured' => in_array($row['name'], self::FEATURED, true),
            'sort_order' => $order,
        ]);

        foreach ($variants as $variant) {
            $this->seedVariant($line, $category, $variant);
        }
    }

    private function seedVariant(ProductLine $line, Category $category, array $v): void
    {
        $brand = Brand::firstOrCreate(['slug' => Str::slug($v['brand'])], ['name' => $v['brand']]);
        $direct = $v['mode'] === 'direct';

        // withTrashed: una referencia eliminada a propósito no se vuelve a crear
        Product::withTrashed()->firstOrCreate(['sku' => $v['sku']], [
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'line_id' => $line->id,
            'name' => $v['name'],
            'variant_label' => $v['label'],
            'slug' => Str::slug($v['name'].' '.$v['sku']),
            'short_description' => Str::limit($line->name.' · '.$v['label'], 480),
            'specs' => array_filter([
                'potencia_hp' => $v['hp'],
                'velocidad_rpm' => $v['rpm'],
                'corriente_a' => $v['amp'],
                'voltaje_v' => $v['volt'],
                'proteccion_ip' => $v['ip'],
            ], fn ($value) => $value !== null && $value !== ''),
            'unit' => ProductUnit::Unidad,
            // Los precios del Excel son simulados; sin venta directa no se publica precio
            'price' => $direct ? $v['price'] : null,
            'price_is_simulated' => $direct,
            'tax_rate' => 19.00,
            'stock' => $v['stock'],
            'sale_mode' => $direct ? SaleMode::Direct : SaleMode::Quote,
            'is_active' => true,
        ]);
    }
}
