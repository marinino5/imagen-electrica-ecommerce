<?php

namespace App\Repositories;

use App\Models\Brand;
use Illuminate\Support\Collection;

class BrandRepository
{
    /** @return Collection<int, string> */
    public function activeNames(): Collection
    {
        return Brand::query()->where('is_active', true)->orderBy('name')->pluck('name');
    }
}
