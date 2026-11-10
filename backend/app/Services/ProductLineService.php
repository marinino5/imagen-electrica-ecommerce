<?php

namespace App\Services;

use App\Models\ProductLine;
use App\Repositories\ProductLineRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductLineService
{
    /** El catálogo entrega todas las líneas de una vez (la tienda las filtra en el navegador): hay unas 60 y puede haber más, por eso 200 por página. */
    public const CATALOG_PER_PAGE = 200;

    public const MAX_PER_PAGE = 200;

    public function __construct(private readonly ProductLineRepository $repository) {}

    /** @return LengthAwarePaginator<int, ProductLine> */
    public function catalog(array $filters, int $perPage = self::CATALOG_PER_PAGE): LengthAwarePaginator
    {
        return $this->repository->paginateCatalog($filters, $perPage);
    }

    /** @throws ModelNotFoundException */
    public function findActiveBySlug(string $slug): ProductLine
    {
        return $this->repository->findActiveBySlug($slug) ?? throw (new ModelNotFoundException)->setModel(ProductLine::class);
    }

    /** @return Collection<int, ProductLine> */
    public function related(ProductLine $line, int $limit = 3): Collection
    {
        return $this->repository->relatedTo($line, $limit);
    }

    public function families(): Collection
    {
        return $this->repository->familiesWithCounts();
    }

    public function facets(): array
    {
        return $this->repository->facets();
    }
}
