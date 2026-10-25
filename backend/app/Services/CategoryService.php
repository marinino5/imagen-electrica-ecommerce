<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Services\Concerns\GeneratesUniqueSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    use GeneratesUniqueSlug;

    public const PER_PAGE = 10;

    public const API_PER_PAGE = 50;

    public function __construct(private readonly CategoryRepository $repository) {}

    /** @return LengthAwarePaginator<int, Category> */
    public function paginate(?string $search): LengthAwarePaginator
    {
        return $this->repository->paginate($search ? trim($search) : null, self::PER_PAGE);
    }

    /** @return LengthAwarePaginator<int, Category> */
    public function paginateActive(?string $search, int $perPage = self::API_PER_PAGE): LengthAwarePaginator
    {
        return $this->repository->paginateActive($search ? trim($search) : null, $perPage);
    }

    /** @return Collection<int, Category> */
    public function parentOptions(?Category $editing = null): Collection
    {
        return $this->repository->roots($editing);
    }

    /** @return Collection<int, Category> */
    public function treeOptions(): Collection
    {
        return $this->repository->allInTreeOrder();
    }

    public function create(array $data): Category
    {
        // El slug se genera una vez y no cambia al renombrar, para no romper enlaces.
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['sort_order'] ??= 0;

        return $this->repository->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $data['sort_order'] ??= 0;

        return $this->repository->update($category, $data);
    }

    /** @throws BusinessRuleException si tiene productos o subcategorías */
    public function delete(Category $category): void
    {
        $products = $this->repository->countProducts($category);

        if ($products > 0) {
            throw new BusinessRuleException(
                "No se puede eliminar «{$category->name}» porque tiene {$products} ".($products === 1 ? 'producto' : 'productos').'. Muévelos a otra categoría primero.'
            );
        }

        $children = $this->repository->countChildren($category);

        if ($children > 0) {
            throw new BusinessRuleException(
                "No se puede eliminar «{$category->name}» porque tiene {$children} ".($children === 1 ? 'subcategoría' : 'subcategorías').'. Elimínalas o muévelas primero.'
            );
        }

        $this->repository->delete($category);
    }

    protected function slugExists(string $slug): bool
    {
        return $this->repository->slugExists($slug);
    }
}
