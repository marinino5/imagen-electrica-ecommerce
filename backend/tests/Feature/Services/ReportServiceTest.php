<?php

namespace Tests\Feature\Services;

use App\Enums\OrderStatus;
use App\Enums\ReportPreset;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\ReportService;
use App\Support\ReportPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private CarbonImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = CarbonImmutable::parse('2026-10-15 12:00:00');
    }

    private function service(): ReportService
    {
        return app(ReportService::class);
    }

    private function period(): ReportPeriod
    {
        return ReportPeriod::resolve(ReportPreset::ThisMonth, now: $this->now);
    }

    /** Pedido pagado con una línea. El precio incluye IVA (19 %). */
    private function sale(Product $product, float $quantity, string $paidAt, OrderStatus $status = OrderStatus::Paid, ?float $total = null): Order
    {
        $lineTotal = round((float) $product->price * $quantity, 2);
        $order = Order::factory()->create([
            'status' => $status,
            'paid_at' => $paidAt,
            'created_at' => $paidAt,
            'total' => $total ?? $lineTotal,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $product->price,
            'line_total' => $lineTotal,
        ]);

        return $order;
    }

    /** Cotización enviada en $sentAt, creada $hoursToRespond horas antes. */
    private function sentQuote(string $sentAt, int $hoursToRespond, OrderStatus $finalStatus = OrderStatus::Sent): Order
    {
        $sent = CarbonImmutable::parse($sentAt);
        $quote = Order::factory()->quote()->create([
            'status' => $finalStatus,
            'created_at' => $sent->subHours($hoursToRespond),
        ]);
        $quote->statusHistories()->make(['from_status' => OrderStatus::InReview, 'to_status' => OrderStatus::Sent])
            ->forceFill(['created_at' => $sent, 'updated_at' => $sent])
            ->save();

        return $quote;
    }

    public function test_sales_orders_and_average_ticket_only_count_paid_or_later_orders_in_the_period(): void
    {
        $product = Product::factory()->create(['price' => 100000]);

        $this->sale($product, 3, '2026-10-02 10:00:00'); // 300.000
        $this->sale($product, 2, '2026-10-05 10:00:00', OrderStatus::Shipped); // 200.000
        $this->sale($product, 1, '2026-10-15 09:00:00', OrderStatus::Delivered); // 100.000, el último día cuenta
        $this->sale($product, 9, '2026-09-10 10:00:00'); // periodo anterior (1 al 15 de septiembre)
        Order::factory()->create(['total' => 777000, 'created_at' => '2026-10-03']); // pendiente de pago
        Order::factory()->create(['status' => OrderStatus::Cancelled, 'total' => 555000, 'created_at' => '2026-10-04']);
        Order::factory()->quote()->create(['status' => OrderStatus::Accepted, 'total' => 999000, 'created_at' => '2026-10-04']);

        $indicators = $this->service()->indicators($this->period());

        $this->assertSame(600000.0, $indicators['sales']['value']);
        $this->assertSame(3, $indicators['orders']['value']);
        $this->assertSame(200000.0, $indicators['average_ticket']['value']);
        // Periodo anterior: un pedido de 900.000
        $this->assertSame(900000.0, $indicators['sales']['previous']);
        $this->assertSame(-33.3, $indicators['sales']['change']);
    }

    public function test_sales_include_shipping_because_they_use_the_order_total(): void
    {
        $product = Product::factory()->create(['price' => 100000]);
        $this->sale($product, 1, '2026-10-02 10:00:00', total: 135000); // 100.000 + 35.000 de envío

        $this->assertSame(135000.0, $this->service()->indicators($this->period())['sales']['value']);
    }

    public function test_gross_profit_and_margin_use_the_base_without_vat_and_skip_products_without_cost(): void
    {
        // Precio 119.000 con IVA = base 100.000. Costo 60.000 => 40.000 de ganancia por unidad
        $withCost = Product::factory()->create(['price' => 119000, 'tax_rate' => 19, 'cost_price' => 60000]);
        $withoutCost = Product::factory()->create(['price' => 238000, 'tax_rate' => 19, 'cost_price' => null]);

        $this->sale($withCost, 10, '2026-10-02 10:00:00');
        $this->sale($withoutCost, 1, '2026-10-03 10:00:00');

        $report = $this->service()->build($this->period(), $this->now);

        $this->assertEqualsWithDelta(400000.0, $report['indicators']['gross_profit']['value'], 0.01);
        $this->assertSame(40.0, $report['indicators']['margin']['value']);
        $this->assertSame(1, $report['without_cost']);
        // Las ventas sí incluyen el producto sin costo
        $this->assertSame(1428000.0, $report['indicators']['sales']['value']);
    }

    public function test_margin_is_null_when_no_sold_product_has_cost(): void
    {
        $this->sale(Product::factory()->create(['price' => 100000, 'cost_price' => null]), 1, '2026-10-02 10:00:00');

        $indicators = $this->service()->indicators($this->period());

        $this->assertNull($indicators['margin']['value']);
        $this->assertNull($indicators['margin']['change']);
    }

    public function test_margin_change_is_measured_in_percentage_points(): void
    {
        $product = Product::factory()->create(['price' => 119000, 'tax_rate' => 19, 'cost_price' => 60000]); // margen 40 %
        $this->sale($product, 1, '2026-10-02 10:00:00');

        $older = Product::factory()->create(['price' => 119000, 'tax_rate' => 19, 'cost_price' => 80000]); // margen 20 %
        $this->sale($older, 1, '2026-09-02 10:00:00');

        $this->assertSame(20.0, $this->service()->indicators($this->period())['margin']['change']);
    }

    public function test_quote_conversion_is_accepted_over_sent_in_the_period(): void
    {
        $this->sentQuote('2026-10-03 10:00:00', 5, OrderStatus::Accepted);
        $this->sentQuote('2026-10-04 10:00:00', 5, OrderStatus::Rejected);
        $this->sentQuote('2026-10-05 10:00:00', 5);
        $this->sentQuote('2026-10-06 10:00:00', 5);
        // Enviada el mes anterior y aceptada en este: pertenece al periodo en que se envió
        $this->sentQuote('2026-09-10 10:00:00', 5, OrderStatus::Accepted);
        // Nueva, nunca enviada
        Order::factory()->quote()->create(['created_at' => '2026-10-07']);

        $indicators = $this->service()->indicators($this->period());

        $this->assertSame(25.0, $indicators['conversion']['value']); // 1 de 4
        $this->assertSame(100.0, $indicators['conversion']['previous']); // 1 de 1
        $this->assertSame(-75.0, $indicators['conversion']['change']); // puntos porcentuales
    }

    public function test_conversion_is_null_without_sent_quotes(): void
    {
        $this->assertNull($this->service()->indicators($this->period())['conversion']['value']);
    }

    public function test_average_quote_response_time_goes_from_creation_to_sending(): void
    {
        $this->sentQuote('2026-10-03 10:00:00', 10);
        $this->sentQuote('2026-10-05 10:00:00', 20);
        $this->sentQuote('2026-09-05 10:00:00', 48); // periodo anterior

        $indicators = $this->service()->indicators($this->period());

        $this->assertSame(15.0, $indicators['response_hours']['value']);
        $this->assertSame(48.0, $indicators['response_hours']['previous']);
    }

    public function test_monthly_chart_compares_the_last_12_months_with_the_year_before(): void
    {
        $product = Product::factory()->create(['price' => 119000, 'tax_rate' => 19, 'cost_price' => 60000]);
        $this->sale($product, 1, '2026-10-02 10:00:00'); // octubre 2026 (último mes)
        $this->sale($product, 2, '2025-10-02 10:00:00'); // octubre 2025
        $this->sale($product, 3, '2026-01-20 10:00:00'); // enero 2026 (primer mes de la ventana: nov 2025 - oct 2026)

        $monthly = $this->service()->charts($this->period(), $this->now)['monthly'];

        $this->assertCount(12, $monthly['labels']);
        $this->assertSame(119000.0, $monthly['sales'][11]);
        $this->assertSame(238000.0, $monthly['sales_previous'][11]);
        $this->assertSame(357000.0, $monthly['sales'][2]); // enero 2026
        $this->assertSame(0.0, $monthly['sales'][5]);
        $this->assertEqualsWithDelta(40000.0, $monthly['profit'][11], 0.01);
        $this->assertEqualsWithDelta(80000.0, $monthly['profit_previous'][11], 0.01);
    }

    public function test_sales_by_category_add_subcategories_to_their_parent_and_follow_the_period(): void
    {
        $parent = Category::factory()->create(['name' => 'Motores eléctricos']);
        $child = Category::factory()->childOf($parent)->create();
        $other = Category::factory()->create(['name' => 'Cables']);

        $this->sale(Product::factory()->create(['category_id' => $child->id, 'price' => 100000]), 2, '2026-10-02 10:00:00');
        $this->sale(Product::factory()->create(['category_id' => $parent->id, 'price' => 100000]), 1, '2026-10-03 10:00:00');
        $this->sale(Product::factory()->create(['category_id' => $other->id, 'price' => 50000]), 1, '2026-10-04 10:00:00');
        $this->sale(Product::factory()->create(['category_id' => $other->id, 'price' => 50000]), 9, '2026-08-04 10:00:00'); // fuera del periodo

        $categories = $this->service()->charts($this->period(), $this->now)['categories'];

        $this->assertSame(['Motores eléctricos', 'Cables'], $categories['labels']);
        $this->assertSame([300000.0, 50000.0], $categories['data']);
    }

    public function test_sales_by_brand_are_sorted_from_highest_to_lowest(): void
    {
        $weg = Brand::factory()->create(['name' => 'WEG']);
        $delta = Brand::factory()->create(['name' => 'Delta']);

        $this->sale(Product::factory()->create(['brand_id' => $delta->id, 'price' => 10000]), 1, '2026-10-02 10:00:00');
        $this->sale(Product::factory()->create(['brand_id' => $weg->id, 'price' => 10000]), 5, '2026-10-02 10:00:00');

        $brands = $this->service()->charts($this->period(), $this->now)['brands'];

        $this->assertSame(['WEG', 'Delta'], $brands['labels']);
        $this->assertSame([50000.0, 10000.0], $brands['data']);
    }

    public function test_categories_and_brands_beyond_the_sixth_are_grouped_as_others(): void
    {
        // 8 marcas con ventas de 80.000, 70.000 ... 10.000
        foreach (range(8, 1) as $n) {
            $brand = Brand::factory()->create(['name' => "Marca {$n}"]);
            $this->sale(Product::factory()->create(['brand_id' => $brand->id, 'price' => $n * 10000]), 1, '2026-10-02 10:00:00');
        }

        $brands = $this->service()->charts($this->period(), $this->now)['brands'];

        $this->assertCount(7, $brands['labels']);
        $this->assertSame('Marca 8', $brands['labels'][0]);
        $this->assertSame('Otros', $brands['labels'][6]);
        $this->assertSame(30000.0, $brands['data'][6]); // Marca 2 + Marca 1: 20.000 + 10.000
        $this->assertTrue($brands['has_other']);
    }

    public function test_charts_without_more_than_six_slices_have_no_others(): void
    {
        $this->sale(Product::factory()->create(['price' => 10000]), 1, '2026-10-02 10:00:00');

        $charts = $this->service()->charts($this->period(), $this->now);

        $this->assertFalse($charts['brands']['has_other']);
        $this->assertNotContains('Otros', $charts['categories']['labels']);
    }

    public function test_monthly_quotes_chart_groups_by_month_of_sending(): void
    {
        $this->sentQuote('2026-10-03 10:00:00', 5, OrderStatus::Accepted);
        $this->sentQuote('2026-10-04 10:00:00', 5);
        $this->sentQuote('2026-09-04 10:00:00', 5, OrderStatus::Accepted);

        $quotes = $this->service()->charts($this->period(), $this->now)['quotes'];

        $this->assertSame([2, 1], [$quotes['sent'][11], $quotes['sent'][10]]);
        $this->assertSame([1, 1], [$quotes['accepted'][11], $quotes['accepted'][10]]);
    }

    public function test_rankings_of_products_quotes_and_customers(): void
    {
        $cheap = Product::factory()->create(['price' => 10000, 'sku' => 'CHEAP']);
        $pricey = Product::factory()->create(['price' => 500000, 'sku' => 'PRICEY']);

        $first = $this->sale($pricey, 2, '2026-10-02 10:00:00'); // 1.000.000
        $this->sale($cheap, 30, '2026-10-03 10:00:00'); // 300.000
        $this->sale($pricey, 1, '2026-10-04 10:00:00'); // otro pedido del mismo cliente
        Order::query()->whereKeyNot($first->id)->update(['nit' => $first->nit, 'company_name' => $first->company_name]);

        foreach ([$cheap, $cheap, $pricey] as $product) {
            $quote = Order::factory()->quote()->create(['created_at' => '2026-10-06']);
            OrderItem::factory()->create(['order_id' => $quote->id, 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => null, 'line_total' => null]);
        }

        $tables = $this->service()->tables($this->period());

        $this->assertSame(['PRICEY', 'CHEAP'], $tables['top_products']->pluck('sku')->all());
        $this->assertSame(3.0, $tables['top_products']->first()->units);
        $this->assertSame(1500000.0, $tables['top_products']->first()->total);

        $this->assertSame(['CHEAP', 'PRICEY'], $tables['top_quoted']->pluck('sku')->all());
        $this->assertSame(2, $tables['top_quoted']->first()->quotes);

        // Los tres pedidos comparten NIT: un solo cliente
        $this->assertCount(1, $tables['top_customers']);
        $this->assertSame(3, $tables['top_customers']->first()->orders);
        $this->assertSame(1800000.0, $tables['top_customers']->first()->total);
    }

    public function test_rankings_are_limited_to_ten_rows(): void
    {
        foreach (Product::factory()->count(12)->create(['price' => 1000]) as $product) {
            $this->sale($product, 1, '2026-10-02 10:00:00');
        }

        $this->assertCount(10, $this->service()->tables($this->period())['top_products']);
    }

    public function test_csv_rows_include_indicators_and_neutralize_spreadsheet_formulas(): void
    {
        $product = Product::factory()->create(['price' => 119000, 'cost_price' => 60000, 'sku' => '=CMD()']);
        $this->sale($product, 1, '2026-10-02 10:00:00');

        $rows = $this->service()->csvRows($this->period());
        $flat = array_merge(...array_filter($rows));

        $this->assertContains('Ventas totales', $flat);
        $this->assertContains(119000, $flat);
        $this->assertContains("'=CMD()", $flat);
        $this->assertNotContains('=CMD()', $flat);
        $this->assertSame('reporte-2026-10-01_2026-10-15.csv', $this->service()->csvFilename($this->period()));
    }
}
