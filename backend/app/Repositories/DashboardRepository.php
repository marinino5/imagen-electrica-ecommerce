<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Consultas de conteo y listados cortos para el dashboard del panel.
 */
class DashboardRepository
{
    /**
     * Cantidad de solicitudes por tipo y estado, en una sola consulta agrupada.
     *
     * @return Collection<int, object{type: string, status: string, total: int}>
     */
    public function countOrdersByTypeAndStatus(): Collection
    {
        return Order::query()
            ->select('type', 'status')
            ->selectRaw('count(*) as total')
            ->groupBy('type', 'status')
            ->toBase()
            ->get();
    }

    /**
     * Solicitudes creadas por día y tipo desde la fecha dada.
     *
     * @return Collection<int, object{day: string, type: string, total: int}>
     */
    public function countOrdersPerDaySince(CarbonInterface $since): Collection
    {
        return Order::query()
            ->selectRaw('date(created_at) as day, type, count(*) as total')
            ->where('created_at', '>=', $since)
            ->groupByRaw('date(created_at), type')
            ->toBase()
            ->get();
    }

    public function countOrders(OrderType $type, OrderStatus $status): int
    {
        return Order::query()->where('type', $type)->where('status', $status)->count();
    }

    /** Sin $to cuenta hasta el momento actual. */
    public function countOrdersCreatedBetween(OrderType $type, CarbonInterface $from, ?CarbonInterface $to = null): int
    {
        return Order::query()
            ->where('type', $type)
            ->where('created_at', '>=', $from)
            ->when($to, fn ($query) => $query->where('created_at', '<', $to))
            ->count();
    }

    public function countActiveProducts(): int
    {
        return Product::query()->where('is_active', true)->count();
    }

    /** Sin $to cuenta hasta el momento actual. */
    public function countProductsCreatedBetween(CarbonInterface $from, ?CarbonInterface $to = null): int
    {
        return Product::query()
            ->where('created_at', '>=', $from)
            ->when($to, fn ($query) => $query->where('created_at', '<', $to))
            ->count();
    }

    /**
     * Cotizaciones en alguno de los estados dados creadas antes de la fecha límite.
     *
     * @param  array<int, OrderStatus>  $statuses
     */
    public function countQuotesCreatedBefore(CarbonInterface $before, array $statuses): int
    {
        return Order::query()
            ->where('type', OrderType::Quote)
            ->whereIn('status', $statuses)
            ->where('created_at', '<', $before)
            ->count();
    }

    /**
     * Cotizaciones en los estados dados, de la más antigua a la más reciente.
     *
     * @param  array<int, OrderStatus>  $statuses
     * @return EloquentCollection<int, Order>
     */
    public function oldestQuotesIn(array $statuses, int $limit): EloquentCollection
    {
        return Order::query()
            ->select('id', 'code', 'status', 'company_name', 'contact_name', 'created_at')
            ->where('type', OrderType::Quote)
            ->whereIn('status', $statuses)
            ->oldest()
            ->limit($limit)
            ->get();
    }

    /** @return EloquentCollection<int, Order> */
    public function latestPurchaseOrders(int $limit): EloquentCollection
    {
        return Order::query()
            ->select('id', 'code', 'status', 'company_name', 'total', 'created_at')
            ->where('type', OrderType::Purchase)
            ->latest()
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Categorías de primer nivel activas con la cantidad de productos activos
     * que tienen ellas y sus subcategorías.
     *
     * @return EloquentCollection<int, Category>
     */
    public function topCategoriesWithProductCount(): EloquentCollection
    {
        $productCount = Product::query()
            ->selectRaw('count(*)')
            ->join('categories as subcategories', 'subcategories.id', '=', 'products.category_id')
            ->where('products.is_active', true)
            ->where(fn ($query) => $query
                ->whereColumn('subcategories.id', 'categories.id')
                ->orWhereColumn('subcategories.parent_id', 'categories.id'));

        return Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->addSelect(['active_products_count' => $productCount])
            ->get();
    }
}
