<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReportPreset;
use App\Enums\OrderType;
use App\Models\Order;
use App\Repositories\DashboardRepository;
use App\Repositories\ReportRepository;
use App\Support\ReportPeriod;
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

    /** Días del gráfico de ventas y cotizaciones (periodo por defecto). */
    public const CHART_DAYS = 30;

    /** Desde este número de días la gráfica agrupa por mes en vez de por día. */
    private const MONTHLY_FROM_DAYS = 93;

    /**
     * Inicio: lo que requiere atención, cuatro indicadores del período (cada uno con su variación frente al período anterior y la serie
     * para su minigráfica), una gráfica de ventas y cotizaciones y el embudo cotización → pedido. El período es el del filtro global.
     *
     * @return array{period: ReportPeriod, kpis: array<string, array>, attention: Collection, chart: array, funnel: array}
     */
    public function overview(?ReportPeriod $period = null, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $period ??= ReportPeriod::resolve(ReportPreset::Last30, now: $now);
        $reports = app(ReportRepository::class);
        $quotes = app(QuoteInboxService::class);

        $sales = $reports->salesTotals($period->start, $period->end);
        $salesBefore = $reports->salesTotals($period->previousStart, $period->previousEnd);
        $funnel = $reports->quoteFunnel($period->start, $period->end);
        $funnelBefore = $reports->quoteFunnel($period->previousStart, $period->previousEnd);
        $received = $reports->quotesReceived($period->start, $period->end);
        $receivedBefore = $reports->quotesReceived($period->previousStart, $period->previousEnd);
        $receivedTotal = $received->account + $received->free;
        $purchasesBefore = array_sum($this->repository->purchasesPerDay($period->previousStart, $period->previousEnd)->all());

        [$labels, $salesSeries, $purchaseSeries, $quoteSeries] = $this->series($period);
        $purchasesNow = array_sum($purchaseSeries);

        return [
            'period' => $period,
            'kpis' => [
                'sales' => ['label' => 'Ventas', 'value' => $sales->sales, 'format' => 'money', 'previous' => $salesBefore->sales, 'change' => $this->change($sales->sales, $salesBefore->sales), 'series' => $salesSeries, 'note' => $sales->orders.' pedidos pagados', 'lower_is_better' => false],
                'orders' => ['label' => 'Pedidos nuevos', 'value' => $purchasesNow, 'format' => 'number', 'previous' => $purchasesBefore, 'change' => $this->change($purchasesNow, $purchasesBefore), 'series' => $purchaseSeries, 'note' => $this->repository->countOrders(OrderType::Purchase, OrderStatus::PendingPayment).' pendientes de pago', 'lower_is_better' => false],
                'quotes' => ['label' => 'Cotizaciones recibidas', 'value' => $receivedTotal, 'format' => 'number', 'previous' => $receivedBefore->account + $receivedBefore->free, 'change' => $this->change($receivedTotal, $receivedBefore->account + $receivedBefore->free), 'series' => $quoteSeries, 'note' => $quotes->countPending().' sin responder (con y sin cuenta)', 'lower_is_better' => false],
                'response' => ['label' => 'Tiempo medio de respuesta', 'value' => $funnel->avg_response_hours, 'format' => 'hours', 'previous' => $funnelBefore->avg_response_hours, 'change' => $this->change($funnel->avg_response_hours, $funnelBefore->avg_response_hours), 'series' => [], 'note' => 'Cotizaciones respondidas en el período', 'lower_is_better' => true],
            ],
            'attention' => $this->attention($now, $quotes),
            'chart' => ['labels' => $labels, 'sales' => $salesSeries, 'quotes' => $quoteSeries, 'monthly' => $period->days() >= self::MONTHLY_FROM_DAYS],
            'funnel' => $this->funnel($receivedTotal, $funnel->sent, $funnel->accepted, $reports->paidOrdersFromQuotes($period->start, $period->end)),
        ];
    }

    /** Cambio porcentual frente al período anterior; nulo si no hay base para comparar. */
    private function change(float|int|null $value, float|int|null $previous): ?float
    {
        return $value === null || $previous === null || (float) $previous === 0.0 ? null : round(($value - $previous) / abs($previous) * 100, 1);
    }

    /**
     * Embudo cotización → pedido: cuántas llegaron, cuántas se respondieron con precio, cuántas se aceptaron y cuántos pedidos se pagaron.
     * Cada etapa dice qué porcentaje es de la anterior.
     *
     * @return list<array{label: string, value: int, of_previous: ?float, of_first: ?float}>
     */
    private function funnel(int $received, int $answered, int $accepted, int $paid): array
    {
        $stages = [['Cotizaciones recibidas', $received], ['Respondidas con precio', $answered], ['Aceptadas por el cliente', $accepted], ['Pedidos pagados', $paid]];

        return array_map(fn (array $s, int $i) => [
            'label' => $s[0], 'value' => $s[1],
            'of_previous' => $i === 0 || $stages[$i - 1][1] === 0 ? null : round($s[1] / $stages[$i - 1][1] * 100, 1),
            'of_first' => $i === 0 || $received === 0 ? null : round($s[1] / $received * 100, 1),
        ], $stages, array_keys($stages));
    }

    /**
     * Ventas, pedidos y cotizaciones por día del período (o por mes si el período es largo).
     *
     * @return array{0: list<string>, 1: list<float>, 2: list<int>, 3: list<int>} etiquetas, ventas, pedidos, cotizaciones
     */
    private function series(ReportPeriod $period): array
    {
        $sales = $this->repository->salesPerDaySince($period->start, $period->end);
        $purchases = $this->repository->purchasesPerDay($period->start, $period->end);
        $quotes = $this->repository->quotesPerDaySince($period->start, $period->end);

        $days = collect(range(0, $period->days() - 1))->map(fn (int $i) => $period->start->addDays($i));

        if ($period->days() < self::MONTHLY_FROM_DAYS) {
            return [
                $days->map(fn (CarbonImmutable $d) => $d->locale('es')->isoFormat('D MMM'))->all(),
                $days->map(fn (CarbonImmutable $d) => round($sales->get($d->toDateString(), 0.0), 2))->all(),
                $days->map(fn (CarbonImmutable $d) => $purchases->get($d->toDateString(), 0))->all(),
                $days->map(fn (CarbonImmutable $d) => $quotes->get($d->toDateString(), 0))->all(),
            ];
        }

        $months = $days->groupBy(fn (CarbonImmutable $d) => $d->format('Y-m'));
        $sum = fn ($source, $default) => $months->map(fn ($ds) => $ds->sum(fn (CarbonImmutable $d) => $source->get($d->toDateString(), $default)))->values();

        return [
            $months->keys()->map(fn (string $m) => ucfirst(CarbonImmutable::parse($m.'-01')->locale('es')->isoFormat('MMM YYYY')))->all(),
            $sum($sales, 0.0)->map(fn ($v) => round($v, 2))->all(),
            $sum($purchases, 0)->all(),
            $sum($quotes, 0)->all(),
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
