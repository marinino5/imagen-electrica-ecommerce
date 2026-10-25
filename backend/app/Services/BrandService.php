<?php

namespace App\Services;

use App\Repositories\BrandRepository;
use Illuminate\Support\Collection;

class BrandService
{
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
}
