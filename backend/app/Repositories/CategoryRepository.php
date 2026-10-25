<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Concerns\EscapesLike;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository
{
    use EscapesLike;

    /**
     * Listado en orden de árbol: cada categoría padre seguida de sus subcategorías.
     *
     * @return LengthAwarePaginator<int, Category>
     */
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return Category::query()
            ->with('parent:id,name,slug')
            ->withCount('products')
            ->when($search, function ($query, string $term) {
                $like = $this->likePattern($term);
                $query->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('slug', 'like', $like));
            })
            ->orderByRaw('coalesce(parent_id, id), parent_id is not null, sort_order, name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Categorías de primer nivel: las únicas que pueden ser "padre".
     *
     * @return Collection<int, Category>
     */
    public function roots(?Category $except = null): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->when($except, fn ($query, Category $category) => $query->whereKeyNot($category->getKey()))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Todas las categorías en orden de árbol, con el nombre del padre
     * (para los selectores de los formularios y filtros de productos).
     *
     * @return Collection<int, Category>
     */
    public function allInTreeOrder(): Collection
    {
        return Category::query()
            ->with('parent:id,name')
            ->orderByRaw('coalesce(parent_id, id), parent_id is not null, sort_order, name')
            ->get(['id', 'parent_id', 'name']);
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function slugExists(string $slug): bool
    {
        return Category::query()->where('slug', $slug)->exists();
    }

    /** Cuenta también los productos eliminados (borrado lógico): siguen apuntando a la categoría. */
    public function countProducts(Category $category): int
    {
        return $category->products()->withTrashed()->count();
    }

    public function countChildren(Category $category): int
    {
        return $category->children()->count();
    }
}
