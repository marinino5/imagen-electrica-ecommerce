<?php

namespace App\Services;

use App\Repositories\ReportRepository;
use App\Support\ReportPeriod;
use Carbon\CarbonImmutable;

/**
 * Cálculos de la página Reportes (solo administrador): indicadores del periodo
 * comparados con el anterior, datos de los gráficos, tablas de ranking y CSV.
 */
class ReportService
{
    /** Meses que muestran los gráficos mensuales. */
    public const CHART_MONTHS = 12;

    /** Filas de cada tabla de ranking. */
    public const TOP_SIZE = 10;

    /** Categorías o marcas que muestran los gráficos; el resto se agrupa en "Otros". */
    public const CHART_SLICES = 5;

    public function __construct(private readonly ReportRepository $repository) {}

    /**
     * Todo lo que muestra la página para el periodo elegido.
     *
     * @return array{
     *     period: ReportPeriod,
     *     indicators: array<string, array<string, mixed>>,
     *     without_cost: int,
     *     charts: array<string, mixed>,
     *     tables: array<string, mixed>
     * }
     */
    public function build(ReportPeriod $period, ?CarbonImmutable $now = null): array
    {
        return [
            'period' => $period,
            'indicators' => $this->indicators($period),
            'without_cost' => $this->snapshot($period->start, $period->end)['without_cost'],
            'charts' => $this->charts($period, $now ?? CarbonImmutable::now()),
            'tables' => $this->tables($period),
        ];
    }

    /**
     * Cada indicador trae su valor, el del periodo anterior y el cambio:
     * en % para ventas, ganancia, pedidos, ticket y tiempo de respuesta, y en
     * puntos porcentuales para el margen y la conversión (que ya son %).
     *
     * @return array<string, array{label: string, value: float|int|null, previous: float|int|null, change: float|null, format: string, lower_is_better: bool}>
     */
    public function indicators(ReportPeriod $period): array
    {
        $now = $this->snapshot($period->start, $period->end);
        $before = $this->snapshot($period->previousStart, $period->previousEnd);

        $indicator = fn (string $key, string $label, string $format, bool $points = false, bool $lowerIsBetter = false) => [
            $key => [
                'label' => $label,
                'value' => $now[$key],
                'previous' => $before[$key],
                'change' => $points ? $this->pointsChange($now[$key], $before[$key]) : $this->percentChange($now[$key], $before[$key]),
                'format' => $format,
                'lower_is_better' => $lowerIsBetter,
            ],
        ];

        return $indicator('sales', 'Ventas totales', 'money')
            + $indicator('gross_profit', 'Ganancia bruta', 'money')
            + $indicator('tax', 'IVA de las ventas', 'money')
            + $indicator('margin', 'Margen', 'percent', points: true)
            + $indicator('orders', 'Pedidos', 'number')
            + $indicator('average_ticket', 'Ticket promedio', 'money')
            + $indicator('conversion', 'Conversión de cotizaciones', 'percent', points: true)
            + $indicator('response_hours', 'Respuesta a cotizaciones', 'hours', lowerIsBetter: true);
    }

    /**
     * Cifras de un rango. La ganancia y el margen solo usan productos con costo:
     * margen = (base sin IVA - costo) / base sin IVA, sobre esas mismas líneas.
     *
     * @return array{sales: float, tax: float, gross_profit: float, margin: ?float, orders: int, average_ticket: ?float, conversion: ?float, response_hours: ?float, without_cost: int, sent: int, accepted: int}
     */
    /**
     * Análisis comercial del periodo: qué se cotiza y qué se vende (por línea y por familia), cuántas cotizaciones llegan y se
     * aceptan, qué fichas se descargan (lo más consultado en la tienda) y cómo van los suscriptores.
     *
     * @return array<string, mixed>
     */
    public function commercial(ReportPeriod $period, int $limit = 8): array
    {
        [$from, $to] = [$period->start, $period->end];
        $received = $this->repository->quotesReceived($from, $to);
        $total = $received->account + $received->free;

        return [
            'period' => $period,
            'quoted_lines' => $this->repository->topLines('quote', $from, $to, $limit),
            'quoted_families' => $this->repository->topFamilies('quote', $from, $to, $limit),
            'sold_lines' => $this->repository->topLines('purchase', $from, $to, $limit),
            'sold_families' => $this->repository->topFamilies('purchase', $from, $to, $limit),
            'datasheets' => ['total' => $this->repository->datasheetCount($from, $to), 'top' => $this->repository->topDatasheets($from, $to, $limit)],
            'quotes' => [
                'received' => $total, 'account' => $received->account, 'free' => $received->free, 'accepted' => $received->accepted,
                // Cotizaciones con cuenta aceptadas sobre las con cuenta recibidas (las de sin cuenta se cierran fuera de la tienda)
                'conversion' => $received->account > 0 ? round($received->accepted / $received->account * 100, 1) : null,
            ],
            'subscribers' => $this->repository->subscribers($from, $to),
        ];
    }

    public function snapshot(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $sales = $this->repository->salesTotals($from, $to);
        $profit = $this->repository->profitTotals($from, $to);
        $funnel = $this->repository->quoteFunnel($from, $to);

        $grossProfit = $profit->revenue - $profit->cost;

        return [
            'sales' => round($sales->sales, 2),
            'gross_profit' => round($grossProfit, 2),
            'tax' => round($sales->tax, 2),
            'margin' => $profit->revenue > 0 ? round($grossProfit / $profit->revenue * 100, 1) : null,
            'orders' => $sales->orders,
            'average_ticket' => $sales->orders > 0 ? round($sales->sales / $sales->orders, 2) : null,
            'conversion' => $funnel->sent > 0 ? round($funnel->accepted / $funnel->sent * 100, 1) : null,
            'response_hours' => $funnel->avg_response_hours,
            'without_cost' => $profit->without_cost,
            'sent' => $funnel->sent,
            'accepted' => $funnel->accepted,
        ];
    }

    /**
     * Datos para Chart.js. Los dos primeros gráficos son de los últimos 12
     * meses (no dependen del filtro); categoría y marca sí siguen el periodo.
     *
     * @return array{monthly: array, categories: array, brands: array, quotes: array}
     */
    public function charts(ReportPeriod $period, CarbonImmutable $now): array
    {
        $months = $this->lastMonths($now);
        $firstMonth = $months[0];
        $afterLast = $months[self::CHART_MONTHS - 1]->addMonth();

        // Una sola consulta trae los 24 meses: los 12 actuales y los 12 del año anterior
        $monthly = $this->repository->monthlySalesAndProfit($firstMonth->subYear(), $afterLast);
        $pick = fn (string $field, CarbonImmutable $month): float => (float) ($monthly[$month->format('Y-m')]->$field ?? 0);

        $quotes = $this->repository->monthlyQuotes($firstMonth, $afterLast);


        return [
            'monthly' => [
                'labels' => array_map(fn (CarbonImmutable $month) => $month->locale('es')->isoFormat('MMM YY'), $months),
                'sales' => array_map(fn (CarbonImmutable $month) => $pick('sales', $month), $months),
                'sales_previous' => array_map(fn (CarbonImmutable $month) => $pick('sales', $month->subYear()), $months),
                'profit' => array_map(fn (CarbonImmutable $month) => $pick('profit', $month), $months),
                'profit_previous' => array_map(fn (CarbonImmutable $month) => $pick('profit', $month->subYear()), $months),
            ],
            'categories' => $this->slices($this->repository->salesByCategory($period->start, $period->end)),
            'brands' => $this->slices($this->repository->salesByBrand($period->start, $period->end)),
            'quotes' => [
                'labels' => array_map(fn (CarbonImmutable $month) => $month->locale('es')->isoFormat('MMM YY'), $months),
                'sent' => array_map(fn (CarbonImmutable $month) => $quotes[$month->format('Y-m')]->sent ?? 0, $months),
                'accepted' => array_map(fn (CarbonImmutable $month) => $quotes[$month->format('Y-m')]->accepted ?? 0, $months),
            ],
        ];
    }

    /**
     * Las CHART_SLICES porciones mayores y, si hay más, el resto sumado en "Otros".
     *
     * @param  \Illuminate\Support\Collection<int, object{name: string, total: float}>  $rows  Ordenadas de mayor a menor
     * @return array{labels: list<string>, data: list<float>, has_other: bool}
     */
    private function slices(\Illuminate\Support\Collection $rows): array
    {
        $top = $rows->take(self::CHART_SLICES);
        $rest = $rows->slice(self::CHART_SLICES);
        $labels = $top->pluck('name')->all();
        $data = $top->pluck('total')->all();

        if ($rest->isNotEmpty()) {
            $labels[] = 'Otros';
            $data[] = round($rest->sum('total'), 2);
        }

        return ['labels' => $labels, 'data' => $data, 'has_other' => $rest->isNotEmpty()];
    }

    /** @return array{top_products: \Illuminate\Support\Collection, top_quoted: \Illuminate\Support\Collection, top_customers: \Illuminate\Support\Collection} */
    public function tables(ReportPeriod $period): array
    {
        return [
            'top_products' => $this->repository->topProducts($period->start, $period->end, self::TOP_SIZE),
            'top_quoted' => $this->repository->topQuotedProducts($period->start, $period->end, self::TOP_SIZE),
            'top_customers' => $this->repository->topCustomers($period->start, $period->end, self::TOP_SIZE),
        ];
    }

    /**
     * Filas del CSV del periodo. Los pesos van sin decimales y los demás
     * decimales con coma, para que Excel en español los lea como números.
     *
     * @return list<list<string|int>>
     */
    public function csvRows(ReportPeriod $period): array
    {
        $report = $this->build($period);
        $rows = [
            ['Reporte de Imagen Eléctrica'],
            ['Periodo', $period->label(), $period->start->format('d/m/Y').' al '.$period->lastDay()->format('d/m/Y')],
            ['Comparado con', $period->previousLabel()],
            [],
            ['Indicador', 'Periodo', 'Periodo anterior', 'Cambio'],
        ];

        foreach ($report['indicators'] as $indicator) {
            $points = $indicator['format'] === 'percent';
            $rows[] = [
                $indicator['label'],
                $this->csvValue($indicator['value'], $indicator['format']),
                $this->csvValue($indicator['previous'], $indicator['format']),
                $indicator['change'] === null ? '' : $this->decimal($indicator['change']).($points ? ' pp' : ' %'),
            ];
        }
        $rows[] = ['Productos vendidos sin costo (fuera de la ganancia)', $report['without_cost']];

        $rows[] = [];
        $rows[] = ['Productos más vendidos'];
        $rows[] = ['Referencia', 'Producto', 'Unidad', 'Cantidad', 'Valor vendido (COP)'];
        foreach ($report['tables']['top_products'] as $row) {
            $rows[] = [$this->text($row->sku), $this->text($row->name), $this->text($row->unit), $this->decimal($row->units), (int) round($row->total)];
        }

        $rows[] = [];
        $rows[] = ['Productos más cotizados'];
        $rows[] = ['Referencia', 'Producto', 'Unidad', 'Cotizaciones', 'Cantidad'];
        foreach ($report['tables']['top_quoted'] as $row) {
            $rows[] = [$this->text($row->sku), $this->text($row->name), $this->text($row->unit), $row->quotes, $this->decimal($row->units)];
        }

        $rows[] = [];
        $rows[] = ['Mejores clientes'];
        $rows[] = ['Tipo de documento', 'Documento', 'Cliente', 'Pedidos', 'Comprado (COP)'];
        foreach ($report['tables']['top_customers'] as $row) {
            $rows[] = [$this->text($row->document['short'] ?? ''), $this->text($row->document['number'] ?? ''), $this->text($row->company), $row->orders, (int) round($row->total)];
        }

        $rows[] = [];
        $rows[] = ['Ventas por categoría'];
        $rows[] = ['Categoría', 'Ventas (COP)'];
        foreach (array_map(null, $report['charts']['categories']['labels'], $report['charts']['categories']['data']) as [$name, $total]) {
            $rows[] = [$this->text($name), (int) round($total)];
        }

        $rows[] = [];
        $rows[] = ['Ventas por marca'];
        $rows[] = ['Marca', 'Ventas (COP)'];
        foreach (array_map(null, $report['charts']['brands']['labels'], $report['charts']['brands']['data']) as [$name, $total]) {
            $rows[] = [$this->text($name), (int) round($total)];
        }

        return $rows;
    }

    /**
     * Filas del CSV del análisis comercial: cotizaciones recibidas, conversión, rankings de líneas y familias, fichas y suscriptores.
     *
     * @return list<list<string|int>>
     */
    public function commercialCsvRows(ReportPeriod $period): array
    {
        $r = $this->commercial($period, self::TOP_SIZE);
        $q = $r['quotes'];
        $rows = [
            ['Análisis comercial de Imagen Eléctrica'],
            ['Periodo', $period->label(), $period->start->format('d/m/Y').' al '.$period->lastDay()->format('d/m/Y')],
            [],
            ['Indicador', 'Valor'],
            ['Cotizaciones recibidas', $q['received']],
            ['Con cuenta', $q['account']],
            ['Sin cuenta', $q['free']],
            ['Aceptadas (con cuenta)', $q['accepted']],
            ['Conversión cotización → pedido (%)', $q['conversion'] === null ? '' : $this->decimal($q['conversion'])],
            ['Fichas descargadas', $r['datasheets']['total']],
            ['Suscriptores nuevos', $r['subscribers']->new],
            ['Suscriptores activos', $r['subscribers']->active],
            ['Bajas del boletín', $r['subscribers']->unsubscribed],
        ];

        foreach ([['Líneas más cotizadas', 'quoted_lines', 'Cotizaciones'], ['Familias más cotizadas', 'quoted_families', 'Cotizaciones'], ['Líneas más vendidas', 'sold_lines', 'Pedidos'], ['Familias más vendidas', 'sold_families', 'Pedidos']] as [$title, $key, $count]) {
            $rows[] = [];
            $rows[] = [$title];
            $rows[] = ['Nombre', $count, 'Unidades'];
            foreach ($r[$key] as $row) {
                $rows[] = [$this->text($row->name), $row->requests, $this->decimal($row->units)];
            }
        }

        return $rows;
    }

    public function commercialCsvFilename(ReportPeriod $period): string
    {
        return 'analisis-comercial-'.$period->start->format('Y-m-d').'_'.$period->lastDay()->format('Y-m-d').'.csv';
    }

    public function pdfFilename(string $kind, ReportPeriod $period): string
    {
        return ($kind === 'commercial' ? 'analisis-comercial-' : 'analisis-financiero-').$period->start->format('Y-m-d').'_'.$period->lastDay()->format('Y-m-d').'.pdf';
    }

    public function csvFilename(ReportPeriod $period): string
    {
        return 'reporte-'.$period->start->format('Y-m-d').'_'.$period->lastDay()->format('Y-m-d').'.csv';
    }

    /** Los 12 meses que terminan en el mes de $now, del más antiguo al actual. */
    private function lastMonths(CarbonImmutable $now): array
    {
        $first = $now->startOfMonth()->subMonthsNoOverflow(self::CHART_MONTHS - 1);

        return array_map(fn (int $i) => $first->addMonthsNoOverflow($i), range(0, self::CHART_MONTHS - 1));
    }

    /** Cambio porcentual frente al periodo anterior; nulo si no hay base de comparación. */
    private function percentChange(float|int|null $value, float|int|null $previous): ?float
    {
        if ($value === null || $previous === null || (float) $previous === 0.0) {
            return null;
        }

        return round(($value - $previous) / abs($previous) * 100, 1);
    }

    /** Diferencia en puntos porcentuales (para indicadores que ya son %). */
    private function pointsChange(float|int|null $value, float|int|null $previous): ?float
    {
        return $value === null || $previous === null ? null : round($value - $previous, 1);
    }

    private function csvValue(float|int|null $value, string $format): string|int
    {
        return match (true) {
            $value === null => '',
            $format === 'money' => (int) round($value),
            $format === 'number' => (int) $value,
            $format === 'percent' => $this->decimal($value).' %',
            default => $this->decimal($value).' h',
        };
    }

    private function decimal(float|int $value): string
    {
        return rtrim(rtrim(number_format($value, 2, ',', ''), '0'), ',');
    }

    /**
     * Texto que viene de datos de clientes o del catálogo: si empieza por
     * = + - @ Excel lo ejecutaría como fórmula, así que se antepone una comilla.
     */
    private function text(?string $value): string
    {
        $value ??= '';

        return $value !== '' && str_contains("=+-@\t\r", $value[0]) ? "'".$value : $value;
    }
}
