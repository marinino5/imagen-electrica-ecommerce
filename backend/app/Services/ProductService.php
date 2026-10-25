<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\Concerns\GeneratesUniqueSlug;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    use GeneratesUniqueSlug;

    public const PER_PAGE = 10;

    /** Un producto con este stock o menos se considera de stock bajo. */
    public const LOW_STOCK_THRESHOLD = 5;

    public function __construct(private readonly ProductRepository $repository) {}

    /**
     * @param  array{q?: ?string, category_id?: ?int, brand_id?: ?int, sale_mode?: ?string, stock?: ?string, status?: ?string}  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateFiltered($filters, self::LOW_STOCK_THRESHOLD, self::PER_PAGE);
    }

    public function create(array $data): Product
    {
        $data = $this->prepare($data);
        // El slug se genera una vez y no cambia al renombrar, para no romper enlaces.
        $data['slug'] = $this->uniqueSlug($data['name']);

        return $this->repository->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        return $this->repository->update($product, $this->prepare($data));
    }

    public function delete(Product $product): void
    {
        $this->repository->delete($product);
    }

    public function restore(Product $product): void
    {
        $this->repository->restore($product);
    }

    /**
     * Convierte las filas clave-valor del formulario en el JSON de `specs`.
     * Los valores numéricos se guardan como número ("5" → 5, "2,5" → 2.5) y
     * las filas vacías se descartan. Sin filas, specs queda en null.
     *
     * @param  array<int, array{key?: ?string, value?: ?string}>  $rows
     * @return array<string, int|float|string>|null
     */
    public function specsFromRows(array $rows): ?array
    {
        $specs = [];

        foreach ($rows as $row) {
            $key = trim((string) ($row['key'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));

            if ($key === '' || $value === '') {
                continue;
            }

            $number = str_replace(',', '.', $value);
            $specs[$key] = is_numeric($number) ? $number + 0 : $value;
        }

        return $specs ?: null;
    }

    private function prepare(array $data): array
    {
        $data['specs'] = $this->specsFromRows($data['specs'] ?? []);

        return $data;
    }

    protected function slugExists(string $slug): bool
    {
        return $this->repository->slugExists($slug);
    }
}
