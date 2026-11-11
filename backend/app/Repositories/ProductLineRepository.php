<?php

namespace App\Repositories;

use App\Enums\SaleMode;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLine;
use App\Repositories\Concerns\EscapesLike;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductLineRepository
{
    use EscapesLike;

    /** Columnas de las referencias que hacen falta para resumir una línea (rango de HP, desde $, stock). */
    private const SUMMARY_COLUMNS = ['id', 'line_id', 'sku', 'variant_label', 'name', 'slug', 'brand_id', 'category_id', 'price', 'stock', 'sale_mode', 'unit', 'tax_rate', 'specs', 'is_active'];

    /**
     * Líneas activas con filtros. Claves de $filters (todas opcionales):
     * q (línea, categoría, familia, marca, referencia o sku), family y category (slug),
     * brand (nombre, también en líneas con varias marcas), mode (direct = compra en línea,
     * quote = cotización o bajo pedido), in_stock y hp_min / hp_max (potencia de alguna referencia).
     *
     * @param  array{q?: ?string, family?: ?string, category?: ?string, brand?: ?string, mode?: ?string, in_stock?: bool, hp_min?: ?float, hp_max?: ?float}  $filters
     * @return LengthAwarePaginator<int, ProductLine>
     */
    public function paginateCatalog(array $filters, int $perPage): LengthAwarePaginator
    {
        return ProductLine::query()
            ->with([
                'category:id,name,slug,parent_id', 'category.parent:id,name,slug,description', 'brand:id,name,slug',
                'variants' => fn ($q) => $q->where('is_active', true)->orderBy('id')->select(self::SUMMARY_COLUMNS),
            ])
            ->where('is_active', true)
            ->when($filters['q'] ?? null, function (Builder $query, string $term) {
                $like = $this->likePattern($term);
                $query->where(fn (Builder $q) => $q
                    ->where('name', 'like', $like)
                    ->orWhere('brand_label', 'like', $like)
                    ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', $like)
                        ->orWhereHas('parent', fn (Builder $f) => $f->where('name', 'like', $like)))
                    ->orWhereHas('variants', fn (Builder $v) => $v->where('sku', 'like', $like)
                        ->orWhere('name', 'like', $like)->orWhere('variant_label', 'like', $like)));
            })
            ->when($filters['family'] ?? null, fn (Builder $query, string $slug) => $query->whereHas(
                'category.parent', fn (Builder $f) => $f->where('slug', $slug)
            ))
            ->when($filters['category'] ?? null, fn (Builder $query, string $slug) => $query->whereHas(
                'category', fn (Builder $c) => $c->where('slug', $slug)
            ))
            ->when($filters['brand'] ?? null, fn (Builder $query, string $name) => $query->where('brand_label', 'like', $this->likePattern($name)))
            ->when($filters['mode'] ?? null, fn (Builder $query, string $mode) => match ($mode) {
                'direct' => $query->where('sale_mode', SaleMode::Direct->value),
                'quote' => $query->whereIn('sale_mode', [SaleMode::Quote->value, SaleMode::OnRequest->value]),
                default => $query,
            })
            ->when(($filters['in_stock'] ?? false) && config('shop.track_stock'), fn (Builder $query) => $query->whereHas('variants', fn (Builder $v) => $v->where('stock', '>', 0)))
            ->when(isset($filters['hp_min']) || isset($filters['hp_max']), fn (Builder $query) => $query->whereIn(
                'id', $this->lineIdsWithHp($filters['hp_min'] ?? null, $filters['hp_max'] ?? null)
            ))
            ->orderBy('sort_order')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Ids de las líneas con alguna referencia cuya potencia está entre $min y $max HP. La potencia vive en
     * specs (JSON) y se compara en PHP: así funciona igual en MySQL y en SQLite (pruebas).
     *
     * @return list<int>
     */
    private function lineIdsWithHp(?float $min, ?float $max): array
    {
        return Product::query()
            ->whereNotNull('line_id')
            ->where('is_active', true)
            ->get(['line_id', 'specs'])
            ->filter(function (Product $p) use ($min, $max) {
                $hp = $p->specs['potencia_hp'] ?? null;

                return $hp !== null && ($min === null || $hp >= $min) && ($max === null || $hp <= $max);
            })
            ->pluck('line_id')
            ->unique()
            ->values()
            ->all();
    }

    /** Ficha de una línea activa con todas sus referencias activas. */
    public function findActiveBySlug(string $slug): ?ProductLine
    {
        return ProductLine::query()
            ->with([
                'category:id,name,slug,parent_id', 'category.parent:id,name,slug,description', 'brand:id,name,slug',
                'variants' => fn ($q) => $q->where('is_active', true)->with('brand:id,name,slug')->orderBy('id'),
            ])
            ->where('is_active', true)
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Otras líneas de la misma familia (para "Líneas relacionadas").
     *
     * @return Collection<int, ProductLine>
     */
    public function relatedTo(ProductLine $line, int $limit): Collection
    {
        $familyId = $line->category->parent_id ?? $line->category_id;
        $categoryIds = Category::query()->where('parent_id', $familyId)->pluck('id')->push($familyId);

        return ProductLine::query()
            ->with([
                'category:id,name,slug,parent_id', 'category.parent:id,name,slug,description', 'brand:id,name,slug',
                'variants' => fn ($q) => $q->where('is_active', true)->orderBy('id')->select(self::SUMMARY_COLUMNS),
            ])
            ->where('is_active', true)
            ->whereKeyNot($line->id)
            ->whereIn('category_id', $categoryIds)
            ->orderByRaw('case when category_id = ? then 0 else 1 end', [$line->category_id])
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * Familias activas (categorías sin padre) con sus categorías y el número de líneas de cada una.
     * Solo salen las que tienen líneas: las categorías antiguas sin líneas no aparecen.
     *
     * @return Collection<int, Category>
     */
    public function familiesWithCounts(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->whereHas('children.productLines', fn (Builder $q) => $q->where('is_active', true))
            ->with(['children' => fn ($q) => $q->where('is_active', true)
                ->whereHas('productLines', fn (Builder $l) => $l->where('is_active', true))
                ->withCount(['productLines' => fn (Builder $l) => $l->where('is_active', true)])
                ->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Marcas con su número de líneas ("Siemens · WEG" cuenta para las dos) y totales para el encabezado del catálogo.
     *
     * @return array{brands: list<array{name: string, count: int}>, families: int, lines: int, references: int}
     */
    public function facets(): array
    {
        $labels = ProductLine::query()->where('is_active', true)->whereNotNull('brand_label')->pluck('brand_label');
        $counts = [];

        foreach ($labels as $label) {
            foreach (array_map('trim', explode(' · ', $label)) as $brand) {
                $counts[$brand] = ($counts[$brand] ?? 0) + 1;
            }
        }
        arsort($counts);

        return [
            'brands' => collect($counts)->map(fn (int $count, string $name) => ['name' => $name, 'count' => $count])->values()->all(),
            // Solo familias que se muestran (con al menos una ficha activa): el total sale de los datos, no de un número fijo
            'families' => $this->familiesWithCounts()->count(),
            'lines' => ProductLine::query()->where('is_active', true)->count(),
            'references' => Product::query()->where('is_active', true)->whereNotNull('line_id')->count(),
        ];
    }
}
