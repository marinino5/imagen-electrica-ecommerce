<?php

namespace App\Services;

use App\Repositories\CustomerRepository;
use App\Repositories\OrderRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Buscador global del panel: productos, pedidos y cotizaciones, y clientes en una sola búsqueda. */
class GlobalSearchService
{
    /** Resultados que se muestran de pedidos y de clientes (los productos van paginados). */
    public const QUICK_LIMIT = 8;

    public function __construct(
        private readonly ProductSearchService $products,
        private readonly OrderRepository $orders,
        private readonly CustomerRepository $customers,
    ) {}

    public function isValidTerm(?string $term): bool
    {
        return $this->products->isValidTerm($term);
    }

    /**
     * Null si el texto es muy corto para buscar.
     *
     * @return array{products: LengthAwarePaginator, orders: Collection, customers: Collection}|null
     */
    public function search(?string $term): ?array
    {
        if (! $this->isValidTerm($term)) {
            return null;
        }

        $term = trim((string) $term);

        return [
            'products' => $this->products->search($term),
            'orders' => $this->orders->searchQuick($term, self::QUICK_LIMIT),
            'customers' => $this->customers->searchQuick($term, self::QUICK_LIMIT),
        ];
    }
}
