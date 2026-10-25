<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\Concerns\EscapesLike;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    use EscapesLike;

    /**
     * Busca por nombre o referencia (sku). Los comodines de LIKE del texto
     * escrito se escapan para que "%" o "_" se busquen como texto normal.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function search(string $term, int $perPage): LengthAwarePaginator
    {
        $like = $this->likePattern($term);

        return Product::query()
            ->with(['brand:id,name', 'category:id,name'])
            ->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('sku', 'like', $like))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Listado del panel con filtros. Claves de $filters (todas opcionales):
     * q, category_id (incluye sus subcategorías), brand_id, sale_mode,
     * stock (low = hasta $lowStockThreshold, out = agotado) y
     * status (active, inactive, trashed).
     *
     * @param  array{q?: ?string, category_id?: ?int, brand_id?: ?int, sale_mode?: ?string, stock?: ?string, status?: ?string}  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateFiltered(array $filters, int $lowStockThreshold, int $perPage): LengthAwarePaginator
    {
        return Product::query()
            ->with(['brand:id,name', 'category:id,name'])
            ->when($filters['q'] ?? null, function (Builder $query, string $term) {
                $like = $this->likePattern($term);
                $query->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('sku', 'like', $like));
            })
            ->when($filters['category_id'] ?? null, function (Builder $query, int $categoryId) {
                $ids = Category::query()->where('parent_id', $categoryId)->pluck('id')->push($categoryId);
                $query->whereIn('category_id', $ids);
            })
            ->when($filters['brand_id'] ?? null, fn (Builder $query, int $id) => $query->where('brand_id', $id))
            ->when($filters['sale_mode'] ?? null, fn (Builder $query, string $mode) => $query->where('sale_mode', $mode))
            ->when($filters['stock'] ?? null, fn (Builder $query, string $stock) => match ($stock) {
                'low' => $query->where('stock', '<=', $lowStockThreshold),
                'out' => $query->where('stock', '<=', 0),
                default => $query,
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => match ($status) {
                'active' => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                'trashed' => $query->onlyTrashed(),
                default => $query,
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Catálogo público: solo productos activos. Claves de $filters (todas
     * opcionales): q (nombre o referencia), category (slug; incluye sus
     * subcategorías), brand (slug), sale_mode, featured, in_stock y
     * sort (name, newest, price_asc, price_desc; los que no tienen precio quedan al final).
     *
     * @param  array{q?: ?string, category?: ?string, brand?: ?string, sale_mode?: ?string, featured?: bool, in_stock?: bool, sort?: ?string}  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateCatalog(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['brand:id,name,slug,logo_url', 'category:id,name,slug'])
            ->where('is_active', true)
            ->when($filters['q'] ?? null, function (Builder $query, string $term) {
                $like = $this->likePattern($term);
                $query->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('sku', 'like', $like));
            })
            ->when($filters['category'] ?? null, function (Builder $query, string $slug) {
                $ids = Category::query()
                    ->where('slug', $slug)
                    ->orWhereHas('parent', fn ($q) => $q->where('slug', $slug))
                    ->pluck('id');
                $query->whereIn('category_id', $ids);
            })
            ->when($filters['brand'] ?? null, fn (Builder $query, string $slug) => $query->whereHas('brand', fn ($q) => $q->where('slug', $slug)))
            ->when($filters['sale_mode'] ?? null, fn (Builder $query, string $mode) => $query->where('sale_mode', $mode))
            ->when($filters['featured'] ?? false, fn (Builder $query) => $query->where('is_featured', true))
            ->when($filters['in_stock'] ?? false, fn (Builder $query) => $query->where('stock', '>', 0));

        match ($filters['sort'] ?? 'name') {
            'newest' => $query->latest()->latest('id'),
            'price_asc' => $query->orderByRaw('price is null')->orderBy('price')->orderBy('name'),
            'price_desc' => $query->orderByRaw('price is null')->orderByDesc('price')->orderBy('name'),
            default => $query->orderBy('name'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    /** Detalle público por slug. Un producto inactivo o eliminado responde 404. */
    public function findActiveBySlugOrFail(string $slug): Product
    {
        return Product::query()
            ->with(['brand:id,name,slug,logo_url', 'category:id,name,slug'])
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * Productos activos (no eliminados) por id, indexados por id.
     *
     * @param  array<int, int>  $ids
     * @return Collection<int, Product>
     */
    public function findActiveByIds(array $ids): Collection
    {
        return Product::query()->where('is_active', true)->whereKey($ids)->get()->keyBy('id');
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }

    /** Borrado lógico: el producto sigue existiendo para los pedidos antiguos. */
    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function restore(Product $product): void
    {
        $product->restore();
    }

    /** Incluye los eliminados: el slug es único en la tabla completa. */
    public function slugExists(string $slug): bool
    {
        return Product::withTrashed()->where('slug', $slug)->exists();
    }

    /**
     * Trae los productos (incluso eliminados) bloqueados hasta que termine
     * la transacción, para que dos pagos simultáneos no descuenten el mismo stock.
     *
     * @param  array<int, int>  $ids
     * @return Collection<int, Product>
     */
    public function lockMany(array $ids): Collection
    {
        return Product::withTrashed()->whereKey($ids)->lockForUpdate()->get()->keyBy('id');
    }

    public function decrementStock(Product $product, int $quantity): void
    {
        $product->decrement('stock', $quantity);
    }
}
