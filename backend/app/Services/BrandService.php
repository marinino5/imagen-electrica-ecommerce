<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Brand;
use App\Repositories\BrandRepository;
use App\Services\Concerns\GeneratesUniqueSlug;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BrandService
{
    use GeneratesUniqueSlug;

    public const PER_PAGE = 10;

    public function __construct(private readonly BrandRepository $repository) {}

    /**
     * Nombres de las marcas activas (franja de marcas del login).
     *
     * @return Collection<int, string>
     */
    public function activeNames(): Collection
    {
        return $this->repository->activeNames();
    }

    /** @return LengthAwarePaginator<int, Brand> */
    public function paginate(?string $search): LengthAwarePaginator
    {
        return $this->repository->paginate($search ? trim($search) : null, self::PER_PAGE);
    }

    /** @return EloquentCollection<int, Brand> */
    public function options(): EloquentCollection
    {
        return $this->repository->allOrdered();
    }

    public function create(array $data): Brand
    {
        // El slug se genera una vez y no cambia al renombrar, para no romper enlaces.
        $data['slug'] = $this->uniqueSlug($data['name']);

        return $this->repository->create($data);
    }

    public function update(Brand $brand, array $data): Brand
    {
        return $this->repository->update($brand, $data);
    }

    /** @throws BusinessRuleException si la marca tiene productos */
    public function delete(Brand $brand): void
    {
        $products = $this->repository->countProducts($brand);

        if ($products > 0) {
            throw new BusinessRuleException(
                "No se puede eliminar «{$brand->name}» porque tiene {$products} ".($products === 1 ? 'producto' : 'productos').'. Cámbialos de marca primero.'
            );
        }

        $this->repository->delete($brand);
    }

    protected function slugExists(string $slug): bool
    {
        return $this->repository->slugExists($slug);
    }
}
