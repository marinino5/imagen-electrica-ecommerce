<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use Carbon\CarbonInterface;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

/**
 * Consultas agregadas de los reportes. Todas reciben el rango como
 * [$from, $to): inclusivo al inicio y exclusivo al final.
 *
 * "Venta" = pedido pagado o posterior (pagado, enviado, entregado), fechado
 * en el momento del pago. Los importes de línea incluyen IVA; para la ganancia
 * se usa la base sin IVA (line_total / (1 + IVA)) frente al costo del producto.
 */
class ReportRepository
{
    /** Estados que cuentan como venta. */
    public const SOLD_STATUSES = [OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Delivered];

    /** Fecha de la venta: el pago (con la creación como respaldo si falta paid_at). */
    private const SALE_DATE = 'coalesce(orders.paid_at, orders.created_at)';

    /** Base sin IVA de una línea. */
    private const LINE_BASE = 'order_items.line_total / (1 + order_items.tax_rate / 100.0)';

    /** @return object{orders: int, sales: float} */
    public function salesTotals(CarbonInterface $from, CarbonInterface $to): object
    {
        $row = $this->sales($from, $to)
            ->selectRaw('count(*) as orders, coalesce(sum(orders.total), 0) as sales')
            ->first();

        return (object) ['orders' => (int) $row->orders, 'sales' => (float) $row->sales];
    }

    /**
     * Ventas de productos con costo definido: base sin IVA y costo. También
     * cuántas referencias vendidas no tienen costo (quedan fuera de la ganancia).
     *
     * @return object{revenue: float, cost: float, without_cost: int}
     */
    public function profitTotals(CarbonInterface $from, CarbonInterface $to): object
    {
        $row = $this->soldItems($from, $to)
            ->selectRaw('coalesce(sum(case when products.cost_price is not null then '.self::LINE_BASE.' end), 0) as revenue')
            ->selectRaw('coalesce(sum(case when products.cost_price is not null then products.cost_price * order_items.quantity end), 0) as cost')
            ->selectRaw('count(distinct case when products.cost_price is null then order_items.sku end) as without_cost')
            ->first();

        return (object) [
            'revenue' => (float) $row->revenue,
            'cost' => (float) $row->cost,
            'without_cost' => (int) $row->without_cost,
        ];
    }

    /**
     * Cotizaciones enviadas en el rango y cuántas de ellas terminaron aceptadas.
     * Se agrupan por el mes en que se enviaron, así la conversión nunca pasa de 100 %.
     *
     * @return object{sent: int, accepted: int, avg_response_hours: ?float}
     */
    public function quoteFunnel(CarbonInterface $from, CarbonInterface $to): object
    {
        $row = $this->sentQuotes($from, $to)
            ->selectRaw('count(distinct orders.id) as sent')
            ->selectRaw('count(distinct case when orders.status = ? then orders.id end) as accepted', [OrderStatus::Accepted->value])
            ->selectRaw('avg('.$this->secondsBetween('orders.created_at', 'order_status_histories.created_at').') as avg_seconds')
            ->first();

        return (object) [
            'sent' => (int) $row->sent,
            'accepted' => (int) $row->accepted,
            'avg_response_hours' => $row->avg_seconds === null ? null : round((float) $row->avg_seconds / 3600, 1),
        ];
    }

    /**
     * Ventas y ganancia por mes ("2026-03" => ...), solo meses con ventas.
     *
     * @return Collection<string, object{month: string, sales: float, profit: float}>
     */
    public function monthlySalesAndProfit(CarbonInterface $from, CarbonInterface $to): Collection
    {
        $sales = $this->sales($from, $to)
            ->selectRaw($this->monthOf(self::SALE_DATE).' as month, coalesce(sum(orders.total), 0) as sales')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $profit = $this->soldItems($from, $to)
            ->selectRaw($this->monthOf(self::SALE_DATE).' as month')
            ->selectRaw('coalesce(sum(case when products.cost_price is not null then '.self::LINE_BASE.' - products.cost_price * order_items.quantity end), 0) as profit')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        return $sales->map(fn (object $row, string $month) => (object) [
            'month' => $month,
            'sales' => (float) $row->sales,
            'profit' => (float) ($profit[$month]->profit ?? 0),
        ]);
    }

    /**
     * Cotizaciones enviadas y aceptadas por mes de envío ("2026-03" => ...).
     *
     * @return Collection<string, object{month: string, sent: int, accepted: int}>
     */
    public function monthlyQuotes(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $this->sentQuotes($from, $to)
            ->selectRaw($this->monthOf('order_status_histories.created_at').' as month')
            ->selectRaw('count(distinct orders.id) as sent')
            ->selectRaw('count(distinct case when orders.status = ? then orders.id end) as accepted', [OrderStatus::Accepted->value])
            ->groupBy('month')
            ->get()
            ->map(fn (object $row) => (object) ['month' => $row->month, 'sent' => (int) $row->sent, 'accepted' => (int) $row->accepted])
            ->keyBy('month');
    }

    /**
     * Ventas (con IVA, sin envío) por categoría principal: una subcategoría
     * suma a su categoría padre.
     *
     * @return Collection<int, object{name: string, total: float}>
     */
    public function salesByCategory(CarbonInterface $from, CarbonInterface $to): Collection
    {
        $name = "coalesce(parents.name, categories.name, 'Sin categoría')";

        return $this->soldItems($from, $to)
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('categories as parents', 'parents.id', '=', 'categories.parent_id')
            ->selectRaw("{$name} as name, coalesce(sum(order_items.line_total), 0) as total")
            ->groupByRaw($name)
            ->orderByDesc('total')
            ->get()
            ->map(fn (object $row) => (object) ['name' => $row->name, 'total' => (float) $row->total]);
    }

    /** @return Collection<int, object{name: string, total: float}> */
    public function salesByBrand(CarbonInterface $from, CarbonInterface $to): Collection
    {
        $name = "coalesce(brands.name, 'Sin marca')";

        return $this->soldItems($from, $to)
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->selectRaw("{$name} as name, coalesce(sum(order_items.line_total), 0) as total")
            ->groupByRaw($name)
            ->orderByDesc('total')
            ->get()
            ->map(fn (object $row) => (object) ['name' => $row->name, 'total' => (float) $row->total]);
    }

    /**
     * Productos más vendidos, por valor vendido (las unidades no se pueden
     * ordenar entre sí: hay metros, cajas y unidades).
     *
     * @return Collection<int, object{sku: string, name: string, unit: string, units: float, total: float}>
     */
    public function topProducts(CarbonInterface $from, CarbonInterface $to, int $limit): Collection
    {
        return $this->soldItems($from, $to)
            ->selectRaw('order_items.sku as sku, max(order_items.product_name) as name, max(order_items.unit) as unit')
            ->selectRaw('sum(order_items.quantity) as units, coalesce(sum(order_items.line_total), 0) as total')
            ->groupBy('order_items.sku')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn (object $row) => (object) [
                'sku' => $row->sku, 'name' => $row->name, 'unit' => $row->unit,
                'units' => (float) $row->units, 'total' => (float) $row->total,
            ]);
    }

    /**
     * Productos más cotizados: en cuántas cotizaciones creadas en el rango aparecen.
     *
     * @return Collection<int, object{sku: string, name: string, unit: string, quotes: int, units: float}>
     */
    public function topQuotedProducts(CarbonInterface $from, CarbonInterface $to, int $limit): Collection
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.type', OrderType::Quote)
            ->where('orders.created_at', '>=', $from)
            ->where('orders.created_at', '<', $to)
            ->toBase()
            ->selectRaw('order_items.sku as sku, max(order_items.product_name) as name, max(order_items.unit) as unit')
            ->selectRaw('count(distinct orders.id) as quotes, sum(order_items.quantity) as units')
            ->groupBy('order_items.sku')
            ->orderByDesc('quotes')
            ->orderByDesc('units')
            ->orderBy('sku')
            ->limit($limit)
            ->get()
            ->map(fn (object $row) => (object) [
                'sku' => $row->sku, 'name' => $row->name, 'unit' => $row->unit,
                'quotes' => (int) $row->quotes, 'units' => (float) $row->units,
            ]);
    }

    /**
     * Mejores clientes por valor comprado. Se agrupan por NIT porque el pedido
     * guarda una copia de los datos de la empresa.
     *
     * @return Collection<int, object{nit: string, company: string, orders: int, total: float}>
     */
    public function topCustomers(CarbonInterface $from, CarbonInterface $to, int $limit): Collection
    {
        return $this->sales($from, $to)
            ->selectRaw('orders.nit as nit, max(orders.company_name) as company, count(*) as orders, coalesce(sum(orders.total), 0) as total')
            ->groupBy('orders.nit')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn (object $row) => (object) [
                'nit' => $row->nit, 'company' => $row->company,
                'orders' => (int) $row->orders, 'total' => (float) $row->total,
            ]);
    }

    /** Pedidos vendidos en el rango. */
    private function sales(CarbonInterface $from, CarbonInterface $to): QueryBuilder
    {
        return Order::query()
            ->where('orders.type', OrderType::Purchase)
            ->whereIn('orders.status', self::SOLD_STATUSES)
            ->whereRaw(self::SALE_DATE.' >= ?', [$from])
            ->whereRaw(self::SALE_DATE.' < ?', [$to])
            ->toBase();
    }

    /**
     * Líneas de pedidos vendidos en el rango, con su producto (puede faltar si
     * se eliminó del todo: el LEFT JOIN conserva la línea).
     */
    private function soldItems(CarbonInterface $from, CarbonInterface $to): QueryBuilder
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.type', OrderType::Purchase)
            ->whereIn('orders.status', self::SOLD_STATUSES)
            ->whereRaw(self::SALE_DATE.' >= ?', [$from])
            ->whereRaw(self::SALE_DATE.' < ?', [$to])
            ->toBase();
    }

    /**
     * Cotizaciones cuyo envío al cliente (cambio a "sent") ocurrió en el rango.
     */
    private function sentQuotes(CarbonInterface $from, CarbonInterface $to): QueryBuilder
    {
        return OrderStatusHistory::query()
            ->join('orders', 'orders.id', '=', 'order_status_histories.order_id')
            ->where('orders.type', OrderType::Quote)
            ->where('order_status_histories.to_status', OrderStatus::Sent)
            ->where('order_status_histories.created_at', '>=', $from)
            ->where('order_status_histories.created_at', '<', $to)
            ->toBase();
    }

    // Las pruebas corren en SQLite y producción en MySQL: estas dos funciones
    // de fecha no tienen sintaxis común.

    /** Expresión SQL que da "2026-03" a partir de una columna de fecha. */
    private function monthOf(string $column): string
    {
        return $this->driver() === 'sqlite' ? "strftime('%Y-%m', {$column})" : "date_format({$column}, '%Y-%m')";
    }

    /** Expresión SQL con los segundos transcurridos entre dos columnas de fecha. */
    private function secondsBetween(string $start, string $end): string
    {
        return $this->driver() === 'sqlite'
            ? "((julianday({$end}) - julianday({$start})) * 86400)"
            : "timestampdiff(second, {$start}, {$end})";
    }

    private function driver(): string
    {
        return Order::query()->getConnection()->getDriverName();
    }
}
