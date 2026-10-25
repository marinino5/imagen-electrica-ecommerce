<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Repositories\DashboardRepository;

/**
 * Indicadores del dashboard del panel.
 */
class DashboardService
{
    /** Horas que puede esperar una cotización antes de considerarse atrasada. */
    public const QUOTE_RESPONSE_HOURS = 24;

    /** Estados en los que el cliente todavía no ha recibido respuesta. */
    public const UNANSWERED_QUOTE_STATUSES = [OrderStatus::New, OrderStatus::InReview];

    public function __construct(private readonly DashboardRepository $repository) {}

    /**
     * @return array{
     *     pending_payment_orders: int,
     *     new_quotes: int,
     *     active_products: int,
     *     overdue_quotes: int,
     *     chart: array{labels: list<string>, datasets: list<array{type: string, label: string, data: list<int>}>}
     * }
     */
    public function getSummary(): array
    {
        // Clave "tipo.estado" => cantidad, por ejemplo "quote.new" => 3
        $counts = $this->repository->countOrdersByTypeAndStatus()
            ->mapWithKeys(fn (object $row) => ["{$row->type}.{$row->status}" => (int) $row->total]);

        $count = fn (OrderType $type, OrderStatus $status): int => $counts->get("{$type->value}.{$status->value}", 0);

        return [
            'pending_payment_orders' => $count(OrderType::Purchase, OrderStatus::PendingPayment),
            'new_quotes' => $count(OrderType::Quote, OrderStatus::New),
            'active_products' => $this->repository->countActiveProducts(),
            'overdue_quotes' => $this->repository->countQuotesCreatedBefore(
                now()->subHours(self::QUOTE_RESPONSE_HOURS),
                self::UNANSWERED_QUOTE_STATUSES,
            ),
            'chart' => $this->requestsByStatusChart($count),
        ];
    }

    /**
     * Datos para el gráfico de barras: una barra por estado (en el orden del enum)
     * y una serie por tipo de solicitud.
     *
     * @param  callable(OrderType, OrderStatus): int  $count
     */
    private function requestsByStatusChart(callable $count): array
    {
        $statuses = OrderStatus::cases();
        $seriesLabels = [OrderType::Purchase->value => 'Pedidos', OrderType::Quote->value => 'Cotizaciones'];

        return [
            'labels' => array_map(fn (OrderStatus $status) => $status->label(), $statuses),
            'datasets' => array_map(fn (OrderType $type) => [
                'type' => $type->value,
                'label' => $seriesLabels[$type->value],
                'data' => array_map(fn (OrderStatus $status) => $count($type, $status), $statuses),
            ], OrderType::cases()),
        ];
    }
}
