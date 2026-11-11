<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLine;
use Database\Seeders\CatalogSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pone una base ya desplegada con la ESTRUCTURA del catálogo de database/data/catalogo.json (generado por
 * docs/inventario/reestructurar_catalogo.py): una línea por producto, con su nombre «qué es + marca + serie», la etiqueta de
 * cada variante y sus especificaciones.
 *
 * Qué toca: de las líneas, nombre, marca, categoría, chips y foto; de cada referencia, su línea, etiqueta, especificaciones,
 * marca y foto. Qué NO toca nunca: SKU, nombre del producto, slug, precio, costo, stock ni si está activo (lo que el equipo
 * cambió en el panel, y lo que ya copiaron los pedidos). Una foto solo se cambia si todavía es una de las de /img/catalogo/.
 * Las líneas que quedan sin referencias y ya no están en el JSON se eliminan (sus enlaces antiguos responden 404).
 * Las líneas de CatalogSeeder::HIDDEN_UNTIL_PHOTO (sin foto oficial) quedan ocultas junto con sus referencias.
 *
 * Idempotente: correrlo dos veces deja lo mismo.
 */
class CatalogLayoutService
{
    /** @return array{lines: int, moved: int, removed: int, hidden: int} */
    public function sync(): array
    {
        $data = json_decode((string) file_get_contents(database_path('data/catalogo.json')), true, 512, JSON_THROW_ON_ERROR);
        $imagesPath = database_path('data/line-images.json');
        $images = is_file($imagesPath) ? json_decode((string) file_get_contents($imagesPath), true, 512, JSON_THROW_ON_ERROR) : [];
        $result = ['lines' => 0, 'moved' => 0, 'removed' => 0, 'hidden' => 0];

        // Crea las líneas y referencias que falten (en una base vieja no hace falta; en una nueva ya está todo)
        (new CatalogSeeder)->run();

        DB::transaction(function () use ($data, $images, &$result) {
            $keep = [];

            foreach ($data['lines'] as $row) {
                $slug = $row['slug'] ?? Str::slug($row['name']);
                $keep[] = $slug;
                $line = ProductLine::where('slug', $slug)->first();
                if (! $line) {
                    continue;
                }

                $category = $this->category($row['family'], $row['category']);
                $mainName = $row['brand_label'] ? explode(' · ', $row['brand_label'])[0] : null;
                $brand = $mainName ? Brand::firstOrCreate(['slug' => Str::slug($mainName)], ['name' => $mainName]) : null;
                $lineImage = $this->imagePath($images[$slug] ?? null);

                $line->fill([
                    'name' => $row['name'],
                    'brand_label' => $row['brand_label'],
                    'brand_id' => $brand?->id,
                    'category_id' => $category->id,
                    'spec_chips' => $row['specs'],
                    'image_key' => $row['image'],
                ]);
                if ($this->managedImage($line->image_url)) {
                    $line->image_url = $lineImage;
                }
                $line->save();
                $result['lines']++;

                foreach ($row['variants'] as $variant) {
                    $product = Product::withTrashed()->where('sku', (string) $variant['sku'])->first();
                    if (! $product) {
                        continue;
                    }

                    $target = $variant['image'] ?? $lineImage;
                    $brandId = Brand::firstOrCreate(['slug' => Str::slug($variant['brand'])], ['name' => $variant['brand']])->id;
                    $product->line_id = $line->id;
                    $product->category_id = $category->id;
                    $product->brand_id = $brandId;
                    $product->variant_label = $variant['label'];
                    $product->specs = $this->specs($variant) ?: null;
                    if ($this->managedImage($product->image_url)) {
                        $product->image_url = $target;
                    }
                    if ($product->isDirty('line_id')) {
                        $result['moved']++;
                    }
                    $product->save();
                }
            }

            // Líneas viejas que quedaron vacías y ya no existen en el catálogo
            ProductLine::whereNotIn('slug', $keep)->get()->each(function (ProductLine $line) use (&$result) {
                if (! Product::withTrashed()->where('line_id', $line->id)->exists()) {
                    $line->delete();
                    $result['removed']++;
                }
            });

            // Sin foto oficial: línea y referencias ocultas
            foreach (CatalogSeeder::HIDDEN_UNTIL_PHOTO as $name) {
                $line = ProductLine::where('name', $name)->first();
                if ($line && ! $line->image_url) {
                    $line->update(['is_active' => false]);
                    Product::where('line_id', $line->id)->update(['is_active' => false]);
                    $result['hidden']++;
                }
            }
        });

        return $result;
    }

    /** @return array<string, mixed> las cinco claves de la placa más las propias de la referencia */
    private function specs(array $v): array
    {
        return array_filter([
            'potencia_hp' => $v['hp'] ?? null,
            'velocidad_rpm' => $v['rpm'] ?? null,
            'corriente_a' => $v['amp'] ?? null,
            'voltaje_v' => $v['volt'] ?? null,
            'proteccion_ip' => $v['ip'] ?? null,
            ...($v['specs'] ?? []),
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function imagePath(array|string|null $entry): ?string
    {
        return is_array($entry) ? ($entry['image'] ?? null) : $entry;
    }

    /** Solo se reemplazan las fotos que puso este proyecto (/img/catalogo/…) o ninguna: una URL externa o una foto cargada a mano no se pisa. */
    private function managedImage(?string $url): bool
    {
        return $url === null || str_starts_with($url, '/img/catalogo/');
    }

    private function category(string $family, string $name): Category
    {
        $parent = Category::where('slug', Str::slug($family))->firstOrFail();

        return Category::firstOrCreate(['slug' => Str::slug($name)], ['parent_id' => $parent->id, 'name' => $name, 'sort_order' => 100, 'is_active' => true]);
    }
}
