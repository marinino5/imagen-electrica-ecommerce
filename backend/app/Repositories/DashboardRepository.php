<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Consultas de conteo para el dashboard del panel.
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

    public function countActiveProducts(): int
    {
        return Product::query()->where('is_active', true)->count();
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
}
