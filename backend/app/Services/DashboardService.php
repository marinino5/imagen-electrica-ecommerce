<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Repositories\DashboardRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Indicadores del dashboard del panel.
 */
class DashboardService
{
    /** Horas que puede esperar una cotización antes de considerarse atrasada. */
    public const QUOTE_RESPONSE_HOURS = 24;

    /** Estados en los que el cliente todavía no ha recibido respuesta. */
    public const UNANSWERED_QUOTE_STATUSES = [OrderStatus::New, OrderStatus::InReview];

    /** Días que muestra el gráfico de barras. */
    public const DAILY_CHART_DAYS = 7;

    /** Un producto con este stock o menos aparece en "Stock bajo". */
    public const LOW_STOCK_THRESHOLD = ProductService::LOW_STOCK_THRESHOLD;

    /** Productos que lista la tarjeta "Stock bajo" (el resto está en "Ver todos"). */
    public const LOW_STOCK_LIST_SIZE = 5;

    public function __construct(private readonly DashboardRepository $repository) {}

    /**
     * Todo lo que muestra el dashboard. Cada indicador trae su valor actual y,
     * para comparar, lo creado en los últimos 7 días frente a los 7 anteriores.
     */
    public function getSummary(): array
    {
        $now = CarbonImmutable::now();
        $weekAgo = $now->subWeek();
        $twoWeeksAgo = $now->subWeeks(2);

        // Clave "tipo.estado" => cantidad, por ejemplo "quote.new" => 3
        $counts = $this->repository->countOrdersByTypeAndStatus()
            ->mapWithKeys(fn (object $row) => ["{$row->type}.{$row->status}" => (int) $row->total]);
        $count = fn (OrderType $type, OrderStatus $status): int => $counts->get("{$type->value}.{$status->value}", 0);

        $weekly = fn (OrderType $type): array => [
            'this_week' => $this->repository->countOrdersCreatedBetween($type, $weekAgo),
            'last_week' => $this->repository->countOrdersCreatedBetween($type, $twoWeeksAgo, $weekAgo),
        ];

        $attentionQuotes = $this->attentionQuotes($now);

        return [
            'pending_payment_orders' => ['value' => $count(OrderType::Purchase, OrderStatus::PendingPayment)] + $weekly(OrderType::Purchase),
            'new_quotes' => ['value' => $count(OrderType::Quote, OrderStatus::New)] + $weekly(OrderType::Quote),
            'active_products' => [
                'value' => $this->repository->countActiveProducts(),
                'this_week' => $this->repository->countProductsCreatedBetween($weekAgo),
                'last_week' => $this->repository->countProductsCreatedBetween($twoWeeksAgo, $weekAgo),
            ],
            'overdue_quotes' => [
                'value' => $this->repository->countQuotesCreatedBefore(
                    $now->subHours(self::QUOTE_RESPONSE_HOURS),
                    self::UNANSWERED_QUOTE_STATUSES,
                ),
                'oldest_hours' => $attentionQuotes->first()['hours_waiting'] ?? null,
            ],
            'status_chart' => $this->statusChart($count),
            'daily_chart' => $this->dailyChart($now),
            'attention_quotes' => $attentionQuotes,
            'latest_orders' => $this->repository->latestPurchaseOrders(5),
            'low_stock' => [
                'products' => $this->repository->lowStockProducts(self::LOW_STOCK_THRESHOLD, self::LOW_STOCK_LIST_SIZE),
                'total' => $this->repository->countLowStockProducts(self::LOW_STOCK_THRESHOLD),
            ],
        ];
    }

    /** Número de la campana de la barra superior. */
    public function countNewQuotes(): int
    {
        return $this->repository->countOrders(OrderType::Quote, OrderStatus::New);
    }

    /**
     * Cotizaciones sin respuesta, la más antigua primero, con sus horas de espera.
     *
     * @return Collection<int, array{order: Order, hours_waiting: int, is_overdue: bool}>
     */
    private function attentionQuotes(CarbonImmutable $now): Collection
    {
        return $this->repository->oldestQuotesIn(self::UNANSWERED_QUOTE_STATUSES, 6)
            ->map(function (Order $quote) use ($now) {
                $hours = (int) $quote->created_at->diffInHours($now);

                return [
                    'order' => $quote,
                    'hours_waiting' => $hours,
                    'is_overdue' => $hours >= self::QUOTE_RESPONSE_HOURS,
                ];
            })
            ->values();
    }

    /**
     * Dona: una porción por estado con al menos una solicitud (pedidos y cotizaciones).
     *
     * @param  callable(OrderType, OrderStatus): int  $count
     * @return array{labels: list<string>, data: list<int>}
     */
    private function statusChart(callable $count): array
    {
        $totals = collect(OrderStatus::cases())
            ->mapWithKeys(fn (OrderStatus $status) => [
                $status->label() => $count(OrderType::Purchase, $status) + $count(OrderType::Quote, $status),
            ])
            ->filter();

        return ['labels' => $totals->keys()->all(), 'data' => $totals->values()->all()];
    }

    /**
     * Barras: solicitudes creadas por día en los últimos 7 días, separadas por tipo.
     *
     * @return array{labels: list<string>, datasets: list<array{type: string, label: string, data: list<int>}>}
     */
    private function dailyChart(CarbonImmutable $now): array
    {
        $firstDay = $now->startOfDay()->subDays(self::DAILY_CHART_DAYS - 1);
        $days = collect(range(0, self::DAILY_CHART_DAYS - 1))->map(fn (int $offset) => $firstDay->addDays($offset));

        $counts = $this->repository->countOrdersPerDaySince($firstDay)
            ->mapWithKeys(fn (object $row) => ["{$row->day}.{$row->type}" => (int) $row->total]);

        $seriesLabels = [OrderType::Purchase->value => 'Pedidos', OrderType::Quote->value => 'Cotizaciones'];

        return [
            // "lun 20", "mar 21"...
            'labels' => $days->map(fn (CarbonImmutable $day) => $day->locale('es')->isoFormat('ddd D'))->all(),
            'datasets' => array_map(fn (OrderType $type) => [
                'type' => $type->value,
                'label' => $seriesLabels[$type->value],
                'data' => $days->map(fn (CarbonImmutable $day) => $counts->get("{$day->toDateString()}.{$type->value}", 0))->all(),
            ], OrderType::cases()),
        ];
    }
}
