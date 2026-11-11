<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLine;
use App\Support\BulkUpdate;
use Illuminate\Support\Collection;
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
    /** Columnas de la línea que escribe sync() (el resto no se toca). */
    private const LINE_COLUMNS = ['name', 'brand_label', 'brand_id', 'category_id', 'spec_chips', 'image_key', 'image_url'];

    /** Columnas de la referencia que escribe sync(): nunca SKU, nombre, slug, precio, costo, stock ni si está activo. */
    private const PRODUCT_COLUMNS = ['line_id', 'category_id', 'brand_id', 'variant_label', 'specs', 'image_url'];

    /** @return array{lines: int, moved: int, removed: int, hidden: int} */
    public function sync(): array
    {
        $data = json_decode((string) file_get_contents(database_path('data/catalogo.json')), true, 512, JSON_THROW_ON_ERROR);
        $imagesPath = database_path('data/line-images.json');
        $images = is_file($imagesPath) ? json_decode((string) file_get_contents($imagesPath), true, 512, JSON_THROW_ON_ERROR) : [];
        $result = ['lines' => 0, 'moved' => 0, 'removed' => 0, 'hidden' => 0];

        // Una sola familia «Material Eléctrico y Repuestos» (antes eran dos); en una base nueva no hay nada que fusionar
        $this->mergeRepuestosFamilies();

        // Familias: nombre con mayúscula inicial en cada palabra importante y «Otros productos» pasa a «Herramientas y Repuestos»
        // (su slug cambia con él; el enlace antiguo /productos?familia=otros-productos ya no existe)
        $old = Category::where('slug', 'otros-productos')->first();
        if ($old && ! Category::where('slug', 'herramientas-y-repuestos')->exists()) {
            $old->update(['slug' => 'herramientas-y-repuestos']);
        }
        foreach ($data['families'] as $family) {
            Category::where('slug', Str::slug($family['name']))->update(['name' => $family['name']]);
        }

        // Crea las líneas y referencias que falten (en una base vieja no hace falta; en una nueva ya está todo)
        (new CatalogSeeder)->run();
        $this->migrateLegacyCategories();

        DB::transaction(function () use ($data, $images, &$result) {
            $keep = [];

            // Todo se lee una vez y se compara en memoria; solo se escribe lo que cambió, en lotes (ver App\Support\BulkUpdate)
            $lines = ProductLine::all()->keyBy('slug');
            $categories = Category::all()->keyBy('slug');
            $brands = Brand::pluck('id', 'slug')->all();
            $products = Product::withTrashed()->get()->keyBy(fn (Product $p) => (string) $p->sku);
            $lineRows = [];
            $productRows = [];

            $brandId = function (string $name) use (&$brands): int {
                return $brands[Str::slug($name)] ??= Brand::create(['slug' => Str::slug($name), 'name' => $name])->id;
            };

            foreach ($data['lines'] as $row) {
                $slug = $row['slug'] ?? Str::slug($row['name']);
                $keep[] = $slug;
                $line = $lines->get($slug);
                if (! $line) {
                    continue;
                }

                $category = $this->category($row['family'], $row['category'], $categories);
                $mainName = $row['brand_label'] ? explode(' · ', $row['brand_label'])[0] : null;
                $lineImage = $this->imagePath($images[$slug] ?? null);

                $line->fill([
                    'name' => $row['name'],
                    'brand_label' => $row['brand_label'],
                    'brand_id' => $mainName ? $brandId($mainName) : null,
                    'category_id' => $category->id,
                    'spec_chips' => $row['specs'],
                    'image_key' => $row['image'],
                ]);
                if ($this->managedImage($line->image_url)) {
                    $line->image_url = $lineImage;
                }
                if ($line->isDirty()) {
                    $attributes = $line->getAttributes();
                    $lineRows[$line->id] = array_intersect_key($attributes, array_flip(self::LINE_COLUMNS));
                }
                $result['lines']++;

                foreach ($row['variants'] as $variant) {
                    $product = $products->get((string) $variant['sku']);
                    if (! $product) {
                        continue;
                    }

                    $product->line_id = $line->id;
                    $product->category_id = $category->id;
                    $product->brand_id = $brandId($variant['brand']);
                    $product->variant_label = $variant['label'];
                    $product->specs = $this->specs($variant) ?: null;
                    if ($this->managedImage($product->image_url)) {
                        $product->image_url = $variant['image'] ?? $lineImage;
                    }
                    if ($product->isDirty('line_id')) {
                        $result['moved']++;
                    }
                    if ($product->isDirty()) {
                        $productRows[$product->sku] = array_intersect_key($product->getAttributes(), array_flip(self::PRODUCT_COLUMNS));
                    }
                }
            }

            BulkUpdate::run('product_lines', 'id', $lineRows);
            BulkUpdate::run('products', 'sku', $productRows);

            // Líneas viejas que quedaron vacías y ya no existen en el catálogo
            ProductLine::whereNotIn('slug', $keep)->get()->each(function (ProductLine $line) use (&$result) {
                if (! Product::withTrashed()->where('line_id', $line->id)->exists()) {
                    $line->delete();
                    $result['removed']++;
                }
            });

            // La categoría «Insumos y repuestos» (una sola para tres productos) queda vacía: cada producto tiene la suya
            // (comprobaciones por separado: MySQL no deja borrar de `categories` con una subconsulta sobre la misma tabla, error 1093)
            $empty = Category::where('slug', 'insumos-y-repuestos')->first();
            if ($empty && ! $empty->products()->exists() && ! $empty->productLines()->exists() && ! $empty->children()->exists()) {
                $empty->delete();
            }

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

    public const REPUESTOS_FAMILY_SLUG = 'material-electrico-y-repuestos';

    /**
     * Fusiona las familias «Material Eléctrico» y «Herramientas y Repuestos» en una sola, «Material Eléctrico y Repuestos»: las categorías
     * (Tomas e interruptores, Cintas aislantes, Baterías, Cintas de enmascarar, Tapones y conexiones…) pasan a la familia nueva con sus
     * líneas y productos, y las familias viejas se eliminan. Los slugs viejos siguen respondiendo (ver `shop.legacy_family_slugs`).
     * No toca precios, stock ni SKU. Idempotente.
     *
     * @return bool si había algo que fusionar
     */
    public function mergeRepuestosFamilies(): bool
    {
        $roots = fn (string $slug) => Category::whereNull('parent_id')->where('slug', $slug)->first();
        $material = $roots('material-electrico');
        $tools = $roots('herramientas-y-repuestos');

        if (! $material && ! $tools) {
            return false;
        }

        $fields = ['name' => 'Material Eléctrico y Repuestos', 'slug' => self::REPUESTOS_FAMILY_SLUG, 'description' => 'Tomas, cintas, baterías, conexiones y repuestos para instalar y mantener tu planta o taller.'];
        $target = $roots(self::REPUESTOS_FAMILY_SLUG);

        if (! $target) {
            // La familia vieja de materiales se renombra y se queda con su lugar en el menú
            $target = $material ?? $tools;
            $target->update($fields);
        } else {
            $target->update(['name' => $fields['name'], 'description' => $target->description ?: $fields['description']]);
        }

        DB::transaction(function () use ($material, $tools, $target) {
            foreach ([$material, $tools] as $old) {
                if (! $old || $old->is($target)) {
                    continue;
                }
                Category::where('parent_id', $old->id)->update(['parent_id' => $target->id]);
                Product::withTrashed()->where('category_id', $old->id)->update(['category_id' => $target->id]);
                ProductLine::where('category_id', $old->id)->update(['category_id' => $target->id]);
                $old->delete();
            }
        });

        return true;
    }

    /**
     * Categorías de la primera versión (Motorreductores › Sinfín corona, Ventilación › Extractores…) → las de la tienda. Los productos
     * y líneas que tenían se pasan a su categoría nueva y las viejas se eliminan cuando quedan vacías. Idempotente.
     *
     * @return int categorías eliminadas
     */
    public function migrateLegacyCategories(): int
    {
        // slug viejo => slug nuevo (las de arriba son familias antiguas, sin equivalente directo: sus hijas se mueven una a una)
        $map = [
            'sinfin-corona' => 'sinfin-y-corona', 'coaxiales' => 'helicoidales',
            'guardamotores' => 'controles-de-maniobra-y-proteccion',
            'extractores' => 'ventilacion-industrial', 'ventiladores-axiales' => 'ventilacion-industrial', 'ventiladores-de-techo' => 'ventilacion-industrial',
            'reflectores-led' => 'reflectores-y-alumbrado', 'tubos-led' => 'iluminacion-industrial',
            'cables-thhn' => 'conductores-para-instalaciones', 'cables-encauchetados' => 'conductores-para-instalaciones',
            'compresores-verticales' => 'compresores-de-piston',
            'plc-compactos' => 'plc', 'reles-programables' => 'plc',
        ];
        $legacyRoots = ['motorreductores', 'variadores-y-arrancadores', 'ventilacion', 'cables', 'plc', 'energia-solar'];
        $removed = 0;

        // Una sola consulta: si ninguna categoría antigua existe (lo normal tras la primera vez) no hay nada que hacer
        $legacy = [...array_keys($map), 'paneles-solares', 'inversores-solares', ...$legacyRoots];
        if (! Category::whereIn('slug', $legacy)->exists()) {
            return 0;
        }

        foreach ($map as $old => $new) {
            $from = Category::where('slug', $old)->first();
            $to = Category::where('slug', $new)->first();
            if (! $from || ! $to || $from->is($to)) {
                continue;
            }
            Product::withTrashed()->where('category_id', $from->id)->update(['category_id' => $to->id]);
            ProductLine::where('category_id', $from->id)->update(['category_id' => $to->id]);
        }

        // Las hijas vacías primero y luego las raíces antiguas que ya no tienen nada
        foreach ([...array_keys($map), 'paneles-solares', 'inversores-solares', ...$legacyRoots] as $slug) {
            $category = Category::where('slug', $slug)->first();
            // «PLC» y «Compresores de pistón» son categorías de la tienda: solo se borran las que quedan vacías y no son de la tienda
            if (! $category || in_array($slug, ['plc', 'compresores-de-piston'], true)) {
                continue;
            }
            if (! $category->products()->exists() && ! Product::withTrashed()->where('category_id', $category->id)->exists()
                && ! $category->productLines()->exists() && ! $category->children()->exists()) {
                $category->delete();
                $removed++;
            }
        }

        return $removed;
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

    /** @param  Collection<string, Category>  $categories  las categorías ya leídas (por slug); las que faltan se crean y se agregan */
    private function category(string $family, string $name, Collection $categories): Category
    {
        $slug = Str::slug($name);
        if ($existing = $categories->get($slug)) {
            return $existing;
        }

        $parent = $categories->get(Str::slug($family)) ?? throw new \RuntimeException("Falta la familia «{$family}».");
        $category = Category::create(['slug' => $slug, 'parent_id' => $parent->id, 'name' => $name, 'sort_order' => 100, 'is_active' => true]);
        $categories->put($slug, $category);

        return $category;
    }
}
