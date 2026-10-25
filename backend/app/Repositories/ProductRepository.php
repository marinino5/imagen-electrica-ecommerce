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
