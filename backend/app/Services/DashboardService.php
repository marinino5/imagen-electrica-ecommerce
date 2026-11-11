<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Repositories\DashboardRepository;
use App\Repositories\ReportRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Indicadores del dashboard del panel.
 */
class DashboardService
{
    /** Horas que puede esperar una cotización antes de considerarse atrasada. */
    public const QUOTE_RESPONSE_HOURS = OrderService::QUOTE_RESPONSE_HOURS;

    /** Estados en los que el cliente todavía no ha recibido respuesta. */
    public const UNANSWERED_QUOTE_STATUSES = OrderService::UNANSWERED_QUOTE_STATUSES;

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

    /** Días del gráfico de ventas y cotizaciones. */
    public const CHART_DAYS = 30;

    /**
     * Inicio rediseñado: lo que requiere atención primero, cuatro indicadores y una gráfica de 30 días. Sin catálogo ni listas largas.
     *
     * @return array{kpis: array<string, array>, attention: Collection, chart: array}
     */
    public function overview(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $monthStart = $now->startOfMonth();
        $reports = app(ReportRepository::class);
        $sales = $reports->salesTotals($monthStart, $now->addDay()->startOfDay());
        // Mismo tramo del mes anterior, para comparar
        $previous = $reports->salesTotals($monthStart->subMonthNoOverflow(), $monthStart->subMonthNoOverflow()->addDays($now->day));
        $quotes = app(QuoteInboxService::class);
        $funnel = $reports->quoteFunnel($now->subDays(self::CHART_DAYS)->startOfDay(), $now->addDay()->startOfDay());
        $pendingOrders = $this->repository->countOrders(OrderType::Purchase, OrderStatus::PendingPayment);

        return [
            'kpis' => [
                'sales' => ['label' => 'Ventas del mes', 'value' => $sales->sales, 'format' => 'money', 'previous' => $previous->sales, 'note' => $sales->orders.' pedidos pagados'],
                'orders' => ['label' => 'Pedidos nuevos', 'value' => $pendingOrders, 'format' => 'number', 'note' => 'Pendientes de pago'],
                'quotes' => ['label' => 'Cotizaciones pendientes', 'value' => $quotes->countPending(), 'format' => 'number', 'note' => 'Con y sin cuenta, sin responder'],
                'response' => ['label' => 'Tiempo medio de respuesta', 'value' => $funnel->avg_response_hours, 'format' => 'hours', 'note' => 'Cotizaciones de los últimos 30 días'],
            ],
            'attention' => $this->attention($now, $quotes),
            'chart' => $this->salesAndQuotesChart($now),
        ];
    }

    /** Pedidos por atender y cotizaciones sin responder en una sola lista: las atrasadas primero, luego las más antiguas. */
    private function attention(CarbonImmutable $now, QuoteInboxService $quotes): Collection
    {
        $items = collect();

        foreach ($this->repository->purchasesToHandle(6) as $order) {
            $hours = (int) $order->created_at->diffInHours($now);
            $items->push((object) [
                'kind' => 'order', 'code' => $order->code, 'client' => $order->display_name.($order->is_guest && $order->customer_id === null ? ' (invitado)' : ''), 'status_label' => $order->status->label(), 'order' => $order,
                'hours' => $hours, 'overdue' => $order->status === OrderStatus::PendingPayment && $hours >= 48,
                'url' => route('admin.orders.show', $order), 'can_confirm_payment' => $order->status === OrderStatus::PendingPayment,
            ]);
        }

        foreach ($quotes->pending(6) as $q) {
            $hours = (int) $q->created_at->diffInHours($now);
            $items->push((object) [
                'kind' => 'quote', 'code' => $q->code, 'client' => $q->client, 'status_label' => $q->status_label, 'order' => null,
                'hours' => $hours, 'overdue' => $q->overdue, 'url' => $q->url, 'can_confirm_payment' => false,
            ]);
        }

        return $items->sortBy([['overdue', 'desc'], ['hours', 'desc']])->take(8)->values();
    }

    /** @return array{labels: list<string>, sales: list<float>, quotes: list<int>} */
    private function salesAndQuotesChart(CarbonImmutable $now): array
    {
        $first = $now->startOfDay()->subDays(self::CHART_DAYS - 1);
        $days = collect(range(0, self::CHART_DAYS - 1))->map(fn (int $i) => $first->addDays($i));
        $sales = $this->repository->salesPerDaySince($first);
        $quotes = $this->repository->quotesPerDaySince($first);

        return [
            'labels' => $days->map(fn (CarbonImmutable $d) => $d->locale('es')->isoFormat('D MMM'))->all(),
            'sales' => $days->map(fn (CarbonImmutable $d) => round($sales->get($d->toDateString(), 0.0), 2))->all(),
            'quotes' => $days->map(fn (CarbonImmutable $d) => $quotes->get($d->toDateString(), 0))->all(),
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
     * @return array{labels: list<string>, data: list<int>, statuses: list<string>}
     */
    private function statusChart(callable $count): array
    {
        $totals = collect(OrderStatus::cases())
            ->mapWithKeys(fn (OrderStatus $status) => [
                $status->value => $count(OrderType::Purchase, $status) + $count(OrderType::Quote, $status),
            ])
            ->filter();

        return [
            'labels' => $totals->keys()->map(fn (string $value) => OrderStatus::from($value)->label())->all(),
            'data' => $totals->values()->all(),
            // Con la clave del estado el gráfico usa el mismo color que la insignia
            'statuses' => $totals->keys()->all(),
        ];
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
