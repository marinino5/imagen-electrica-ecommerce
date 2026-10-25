<?php

namespace App\Repositories;

use App\Models\Brand;
use App\Repositories\Concerns\EscapesLike;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BrandRepository
{
    use EscapesLike;

    /** @return Collection<int, string> */
    public function activeNames(): Collection
    {
        return Brand::query()->where('is_active', true)->orderBy('name')->pluck('name');
    }

    /** @return LengthAwarePaginator<int, Brand> */
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return Brand::query()
            ->withCount('products')
            ->when($search, function ($query, string $term) {
                $like = $this->likePattern($term);
                $query->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('slug', 'like', $like));
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @return EloquentCollection<int, Brand> */
    public function allOrdered(): EloquentCollection
    {
        return Brand::query()->orderBy('name')->get(['id', 'name']);
    }

    public function create(array $data): Brand
    {
        return Brand::create($data);
    }

    public function update(Brand $brand, array $data): Brand
    {
        $brand->update($data);

        return $brand;
    }

    public function delete(Brand $brand): void
    {
        $brand->delete();
    }

    public function slugExists(string $slug): bool
    {
        return Brand::query()->where('slug', $slug)->exists();
    }

    /** Cuenta también los productos eliminados (borrado lógico): siguen apuntando a la marca. */
    public function countProducts(Brand $brand): int
    {
        return $brand->products()->withTrashed()->count();
    }
}
