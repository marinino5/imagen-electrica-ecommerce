<?php

namespace Database\Seeders;

use App\Enums\ProductUnit;
use App\Enums\SaleMode;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLine;
use App\Services\CatalogPriceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Catálogo por líneas: familia → categoría → línea (ficha) → referencias (SKU).
 *
 * Los datos salen de database/data/catalogo.json, que se genera con
 * docs/datos/generar_catalogo.py desde el Excel de inventario y el portafolio.
 * Los precios del Excel son de prueba: cada referencia queda con price_is_simulated. Las referencias de la Fase 1B no traen
 * precio en el JSON: reciben un precio de referencia (price_source) al final del seeder, salvo reductores y motorreductores,
 * que se cotizan. Cada una lleva su foto en `image`.
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

    /**
     * Líneas ocultas hasta tener su foto oficial (regla de fotos de producto: nunca se publica sin foto de la marca).
     * Match Drive y los dos productos Siemens no tienen foto oficial disponible; PLC200 tampoco. Al conseguirla, quitar la línea de aquí
     * y activarla en el panel.
     */
    public const HIDDEN_UNTIL_PHOTO = ['Motor monofásico Match Drive', 'Contactor Siemens 3RT2', 'Contacto auxiliar Siemens 3RH2', 'PLC WEG PLC200'];

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

            // Precios de referencia (price_source = referencia) de las referencias nuevas: la empresa los confirma después
            app(CatalogPriceService::class)->loadReferencePrices();
        });
    }

    /** @return array<string, Category> familias por nombre */
    private function seedFamilies(array $families): array
    {
        $result = [];

        foreach ($families as $i => $family) {
            $result[$family['name']] = $category = Category::firstOrCreate(
                ['slug' => Str::slug($family['name'])],
                ['name' => $family['name'], 'description' => $family['description'], 'sort_order' => 100 + $i, 'is_active' => true],
            );

            // Una familia que ya existía como categoría raíz antigua toma el nombre (con mayúsculas) y el orden del portafolio
            if ($category->name !== $family['name']) {
                $category->update(['name' => $family['name']]);
            }

            // (orden del portafolio)
            if ($category->sort_order !== 100 + $i) {
                $category->update(['sort_order' => 100 + $i]);
            }
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
        $mainName = $row['brand_label'] ? explode(' · ', $row['brand_label'])[0] : null;
        $mainBrand = $mainName ? Brand::firstOrCreate(['slug' => Str::slug($mainName)], ['name' => $mainName]) : null;

        $line = ProductLine::firstOrCreate(['slug' => $row['slug'] ?? Str::slug($row['name'])], [
            'category_id' => $category->id,
            'brand_id' => $mainBrand?->id,
            'brand_label' => $row['brand_label'],
            'name' => $row['name'],
            'image_key' => $row['image'],
            'spec_chips' => $row['specs'],
            'sale_mode' => $mode,
            'is_featured' => in_array($row['name'], self::FEATURED, true),
            'is_active' => ! in_array($row['name'], self::HIDDEN_UNTIL_PHOTO, true),
            'sort_order' => $order,
        ]);

        foreach ($variants as $variant) {
            $this->seedVariant($line, $category, $variant);
        }

        // Una línea que estaba «bajo pedido» (sin referencias) y ahora recibe referencias pasa a venderse según ellas:
        // directa si alguna lo es, por cotización si no. Una línea que ya tenía modo propio no se toca.
        if ($variants !== [] && $line->sale_mode === SaleMode::OnRequest) {
            $line->update(['sale_mode' => $mode]);
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
            // Las cinco claves de la placa más las que traiga la referencia en `specs` (pines, potencia_w, longitud_m…).
            // Solo las que salen de la descripción o de la ficha oficial; los campos vacíos no se guardan.
            'specs' => array_filter([
                'potencia_hp' => $v['hp'],
                'velocidad_rpm' => $v['rpm'],
                'corriente_a' => $v['amp'],
                'voltaje_v' => $v['volt'],
                'proteccion_ip' => $v['ip'],
                ...($v['specs'] ?? []),
            ], fn ($value) => $value !== null && $value !== ''),
            'image_url' => $v['image'] ?? null,
            'unit' => ProductUnit::Unidad,
            // Los precios del Excel son simulados; sin venta directa no se publica precio
            'price' => $direct ? $v['price'] : null,
            'price_is_simulated' => $direct,
            'tax_rate' => 19.00,
            'stock' => $v['stock'],
            'sale_mode' => $direct ? SaleMode::Direct : SaleMode::Quote,
            'is_active' => ! in_array($line->name, self::HIDDEN_UNTIL_PHOTO, true),
        ]);
    }
}
