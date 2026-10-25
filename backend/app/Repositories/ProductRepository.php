<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    /**
     * Busca por nombre o referencia (sku). Los comodines de LIKE del texto
     * escrito se escapan para que "%" o "_" se busquen como texto normal.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function search(string $term, int $perPage): LengthAwarePaginator
    {
        $like = '%'.addcslashes($term, '\\%_').'%';

        return Product::query()
            ->with(['brand:id,name', 'category:id,name'])
            ->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('sku', 'like', $like))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
