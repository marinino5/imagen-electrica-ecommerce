<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Buscador global del panel. Por ahora solo busca productos.
 */
class ProductSearchService
{
    /** Con menos letras se devolverían casi todos los productos. */
    public const MIN_TERM_LENGTH = 2;

    public const PER_PAGE = 15;

    public function __construct(private readonly ProductRepository $products) {}

    public function isValidTerm(?string $term): bool
    {
        return mb_strlen(trim((string) $term)) >= self::MIN_TERM_LENGTH;
    }

    /** Devuelve null si el texto es muy corto para buscar. */
    public function search(?string $term): ?LengthAwarePaginator
    {
        if (! $this->isValidTerm($term)) {
            return null;
        }

        return $this->products->search(trim($term), self::PER_PAGE);
    }
}
