<?php

namespace Database\Seeders;

use App\Enums\CustomerType;
use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\SaleMode;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use Carbon\CarbonImmutable;
use Database\Seeders\Concerns\SeedsUsers;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Datos de demostración para la sustentación: pedidos y cotizaciones
 * realistas (con tendencia de crecimiento, temporada y un tiempo de respuesta
 * que mejora) y costos de ejemplo en los productos. Cubre 24 meses: los 12
 * últimos que muestran los gráficos y los 12 anteriores, para que las
 * comparaciones contra el año anterior tengan datos.
 *
 * NO forma parte de DatabaseSeeder. Requiere `php artisan migrate --seed`
 * previo y se ejecuta con:  php artisan db:seed --class=DemoSeeder
 *
 * Es determinista (misma semilla, mismos datos) y no se puede cargar dos veces.
 * Para repetirlo: php artisan migrate:fresh --seed y luego este seeder.
 */
class DemoSeeder extends Seeder
{
    use SeedsUsers;

    private const EMAIL_DOMAIN = 'demo.imagenelectrica.test';

    private const SEED = 20261024;

    /** Meses de historia que se generan (el mes actual y los 23 anteriores). */
    private const MONTHS = 24;

    /** Referencias que se dejan sin costo para mostrar el aviso "productos sin costo". */
    private const SKUS_WITHOUT_COST = ['SYL-T8-18W-C10', 'NEX-THHN-10AWG-R100'];

    /** Empresas ficticias: [empresa, contacto, ciudad, peso (cuánto compran)]. */
    private const COMPANIES = [
        ['Agroindustrial Santander S.A.S.', 'Marcela Ortiz Peña', 'Bucaramanga', 14],
        ['Frigoríficos del Magdalena Medio Ltda.', 'Jorge Iván Prada', 'Barrancabermeja', 10],
        ['Ingeniería y Montajes Eléctricos S.A.S.', 'Andrea Camacho', 'Floridablanca', 8],
        ['Textiles La Cumbre S.A.S.', 'Luis Fernando Mejía', 'Piedecuesta', 6],
        ['Cementos y Prefabricados del Norte S.A.', 'Diana Carolina Rojas', 'Girón', 5],
        ['Panificadora El Trigal S.A.S.', 'Hernán Dario Villamizar', 'Bucaramanga', 3],
        ['Clínica Santa Teresa de Ávila', 'Patricia Duarte', 'Bucaramanga', 2],
        ['Hotel Mansión del Cacique', 'Camilo Andrés Suárez', 'Bucaramanga', 1],
    ];

    /** Factor estacional por mes (enero = 1). */
    private const SEASON = [1 => .85, 2 => .95, 3 => 1.1, 4 => 1.0, 5 => 1.05, 6 => 1.0, 7 => .95, 8 => 1.1, 9 => 1.05, 10 => 1.1, 11 => 1.0, 12 => .8];

    private CarbonImmutable $now;

    private User $commercial;

    /** @var list<Customer> */
    private array $customers = [];

    /** @var list<int> */
    private array $customerWeights = [];

    /** @var list<Product> Venta directa con precio y stock: se compran en la tienda. */
    private array $shelfProducts = [];

    /** @var list<Product> Cotización, bajo pedido o sin stock: solo salen por cotización. */
    private array $quoteProducts = [];

    private OrderRepository $orders;

    private OrderService $orderService;

    public function run(OrderRepository $orders, OrderService $orderService): void
    {
        if (User::where('email', 'like', '%@'.self::EMAIL_DOMAIN)->exists()) {
            $this->command?->warn('Los datos de demostración ya están cargados. Usa migrate:fresh --seed para repetirlos.');

            return;
        }

        $this->orders = $orders;
        $this->orderService = $orderService;
        $this->now = CarbonImmutable::now();
        $this->commercial = User::where('role', UserRole::Comercial)->first()
            ?? throw new RuntimeException('Faltan los datos base. Ejecuta primero: php artisan migrate --seed');

        mt_srand(self::SEED);

        DB::transaction(function () {
            $this->setCosts();
            $this->createCustomers();
            $this->loadProducts();

            $purchases = $quotes = 0;

            // Del mes más antiguo al actual
            for ($monthsAgo = self::MONTHS - 1; $monthsAgo >= 0; $monthsAgo--) {
                $month = $this->now->startOfMonth()->subMonthsNoOverflow($monthsAgo);
                $progress = self::MONTHS - 1 - $monthsAgo; // 0 el mes más antiguo, 23 el actual

                foreach (range(1, $this->monthlyCount(10 + $progress * 0.65, $month)) as $_) {
                    $this->createPurchase($this->randomCustomer(), $this->shelfLines(), $this->randomMoment($month));
                    $purchases++;
                }

                foreach (range(1, $this->monthlyCount(5 + $progress * 0.45, $month)) as $_) {
                    $this->createQuote($this->randomCustomer(), $this->randomMoment($month), $progress);
                    $quotes++;
                }
            }

            $this->command?->info("Datos de demostración: {$purchases} pedidos de tienda y {$quotes} cotizaciones en ".self::MONTHS." meses (más los pedidos que nacieron de cotizaciones aceptadas).");
        });
    }

    // ---------------------------------------------------------------- datos base

    /**
     * Costo de ejemplo sin IVA: entre 68 % y 82 % de la base del precio. Los
     * productos sin precio (por cotización) reciben un costo fijo según su SKU.
     * No pisa costos que ya estén definidos.
     */
    private function setCosts(): void
    {
        Product::whereNull('cost_price')->whereNotIn('sku', self::SKUS_WITHOUT_COST)->each(function (Product $product) {
            $base = $product->price !== null
                ? (float) $product->price / (1 + (float) $product->tax_rate / 100)
                : 700000 + (crc32($product->sku) % 2300000);

            $factor = $product->price !== null ? 0.68 + (crc32('f'.$product->sku) % 15) / 100 : 1;
            $product->forceFill(['cost_price' => round($base * $factor, -2)])->save();
        });
    }

    private function createCustomers(): void
    {
        $password = $this->seedPassword('customer_password');

        // Los dos clientes del seeder base también compran
        $existing = Customer::orderBy('id')->get();
        foreach ($existing as $customer) {
            $this->customers[] = $customer;
            $this->customerWeights[] = 12;
        }

        foreach (self::COMPANIES as $i => [$company, $contact, $city, $weight]) {
            $user = $this->seedUser($contact, 'cliente'.($i + 1).'@'.self::EMAIL_DOMAIN, $password, UserRole::Cliente);

            $this->customers[] = $user->customer()->create([
                'type' => CustomerType::Company,
                'company_name' => $company,
                'nit' => sprintf('9%02d%06d-%d', 10 + $i, 100000 + $i * 7919, $i % 10),
                'contact_name' => $contact,
                'phone' => '31'.($i % 8).' 555 '.sprintf('%04d', 1000 + $i * 37),
                'city' => $city,
                'address' => null,
                'marketing_opt_in' => $i % 3 === 0,
                'marketing_opt_in_at' => $i % 3 === 0 ? now() : null,
                'privacy_accepted_at' => now(),
            ]);
            $this->customerWeights[] = $weight;
        }
    }

    private function loadProducts(): void
    {
        foreach (Product::where('is_active', true)->get() as $product) {
            $isShelf = $product->sale_mode === SaleMode::Direct && $product->price !== null && $product->stock > 0;
            $isShelf ? $this->shelfProducts[] = $product : $this->quoteProducts[] = $product;
        }

        if ($this->shelfProducts === [] || $this->quoteProducts === []) {
            throw new RuntimeException('El catálogo base no tiene productos de venta directa y de cotización. Ejecuta: php artisan migrate --seed');
        }
    }

    // ---------------------------------------------------------------- pedidos

    /**
     * Pedido de la tienda (o el que nace de una cotización aceptada, con $quote).
     * El estado depende de la antigüedad: lo viejo está entregado, lo reciente
     * todavía está en pago o en camino.
     *
     * @param  list<array{0: Product, 1: float|int, 2: float}>  $lines  [producto, cantidad, precio unitario con IVA]
     */
    private function createPurchase(Customer $customer, array $lines, CarbonImmutable $createdAt, ?Order $quote = null): Order
    {
        $status = $this->purchaseStatus($createdAt);
        $order = $this->newOrder(OrderType::Purchase, $status, $customer, $createdAt, [
            'quote_id' => $quote?->id,
            'assigned_to' => $quote?->assigned_to,
            'delivery_method' => $quote?->delivery_method ?? $this->weighted([[DeliveryMethod::Pickup, 40], [DeliveryMethod::Local, 40], [DeliveryMethod::National, 20]]),
        ]);

        $this->addLines($order, $lines);
        $order->forceFill(['shipping' => $this->shippingFor($order)])->save();
        $this->orderService->recalculateTotals($order->load('items'));

        $this->history($order, null, OrderStatus::PendingPayment, $customer->user, $quote ? "Generado al aceptar la cotización {$quote->code}" : 'Pedido creado desde la tienda', $createdAt);

        if ($status === OrderStatus::Cancelled) {
            $this->history($order, OrderStatus::PendingPayment, $status, $customer->user, 'El cliente no completó el pago', $this->cap($createdAt->addDays(3)));

            return $order;
        }

        if ($status === OrderStatus::PendingPayment) {
            return $order;
        }

        $paidAt = $this->cap($createdAt->addHours(mt_rand(4, 40)));
        $order->forceFill(['paid_at' => $paidAt, 'payment_method' => $this->weighted([['transferencia', 70], ['consignación', 30]])])->save();
        $this->history($order, OrderStatus::PendingPayment, OrderStatus::Paid, $this->commercial, 'Pago confirmado', $paidAt);

        if ($status === OrderStatus::Paid) {
            return $order;
        }

        $shippedAt = $this->cap($paidAt->addHours(mt_rand(6, 48)));
        $this->history($order, OrderStatus::Paid, OrderStatus::Shipped, $this->commercial, null, $shippedAt);

        if ($status === OrderStatus::Delivered) {
            $this->history($order, OrderStatus::Shipped, OrderStatus::Delivered, $this->commercial, null, $this->cap($shippedAt->addHours(mt_rand(10, 96))));
        }

        return $order;
    }

    private function purchaseStatus(CarbonImmutable $createdAt): OrderStatus
    {
        $days = $createdAt->diffInDays($this->now);

        return match (true) {
            $days > 25 => $this->weighted([[OrderStatus::Delivered, 93], [OrderStatus::Cancelled, 7]]),
            $days > 5 => $this->weighted([[OrderStatus::Delivered, 60], [OrderStatus::Shipped, 25], [OrderStatus::Paid, 10], [OrderStatus::Cancelled, 5]]),
            default => $this->weighted([[OrderStatus::PendingPayment, 35], [OrderStatus::Paid, 40], [OrderStatus::Shipped, 20], [OrderStatus::Cancelled, 5]]),
        };
    }

    /** @return list<array{0: Product, 1: float|int, 2: float}> */
    private function shelfLines(): array
    {
        $lines = [];

        // Los productos de poco valor (cables, luminarias) se piden más seguido que los motores
        $weights = array_map(fn (Product $product) => [$product, match (true) {
            (float) $product->price < 10000 => 12, // cable por metro
            (float) $product->price < 200000 => 25,
            (float) $product->price < 700000 => 18,
            (float) $product->price < 2000000 => 12,
            default => 6,
        }], $this->shelfProducts);

        foreach (range(1, mt_rand(1, 4)) as $_) {
            $product = $this->weighted($weights);
            $lines[$product->id] = [$product, $this->quantityFor($product), (float) $product->price];
        }

        return array_values($lines);
    }

    private function quantityFor(Product $product): float|int
    {
        return match (true) {
            $product->unit->value === 'metro' => mt_rand(2, 30) * 10,
            $product->unit->value === 'rollo' => mt_rand(1, 4),
            $product->unit->value === 'caja' => mt_rand(1, 8),
            (float) $product->price > 1500000 => mt_rand(1, 3),
            default => mt_rand(1, 6),
        };
    }

    /** Envío: gratis al retirar en la tienda y en compras grandes. */
    private function shippingFor(Order $order): float
    {
        $subtotal = $order->items->sum(fn ($item) => (float) $item->line_total);

        return match (true) {
            $order->delivery_method === DeliveryMethod::Pickup, $subtotal >= 1000000 => 0.0,
            $order->delivery_method === DeliveryMethod::Local => 25000.0,
            default => 45000.0,
        };
    }

    // ---------------------------------------------------------------- cotizaciones

    /**
     * Cotización con su ciclo completo. El tiempo de respuesta mejora con los
     * meses ($progress de 0 a 23) y las aceptadas generan su pedido.
     */
    private function createQuote(Customer $customer, CarbonImmutable $createdAt, int $progress): void
    {
        $quote = $this->newOrder(OrderType::Quote, OrderStatus::New, $customer, $createdAt, [
            'delivery_method' => $this->weighted([[DeliveryMethod::Local, 40], [DeliveryMethod::National, 40], [DeliveryMethod::Pickup, 20]]),
        ]);

        $lines = [];
        foreach (range(1, mt_rand(1, 3)) as $_) {
            $product = $this->quoteProducts[array_rand($this->quoteProducts)];
            $lines[$product->id] = [$product, mt_rand(1, 3), null];
        }
        $this->addLines($quote, array_values($lines));
        $this->history($quote, null, OrderStatus::New, $customer->user, null, $createdAt);

        // 5 % se cancela antes de que el equipo la revise
        if (mt_rand(1, 100) <= 5) {
            $quote->forceFill(['status' => OrderStatus::Cancelled])->save();
            $this->history($quote, OrderStatus::New, OrderStatus::Cancelled, $customer->user, 'El cliente ya no la necesita', $this->cap($createdAt->addHours(mt_rand(2, 30))));

            return;
        }

        // Horas hasta enviar la cotización: de ~60 h al principio a ~18 h hoy
        $responseHours = max(3, (int) round((60 - $progress * 1.8) * mt_rand(40, 160) / 100));
        $reviewAt = $createdAt->addHours(min(mt_rand(1, 10), $responseHours - 1));
        $sentAt = $createdAt->addHours($responseHours);

        if ($reviewAt > $this->now) {
            return; // sigue nueva
        }

        $quote->forceFill(['status' => OrderStatus::InReview, 'assigned_to' => $this->commercial->id])->save();
        $this->history($quote, OrderStatus::New, OrderStatus::InReview, $this->commercial, null, $reviewAt);

        if ($sentAt > $this->now) {
            return; // sigue en revisión
        }

        $this->priceQuote($quote);
        $quote->forceFill(['status' => OrderStatus::Sent, 'expires_at' => $sentAt->addDays(15)])->save();
        $this->history($quote, OrderStatus::InReview, OrderStatus::Sent, $this->commercial, 'Cotización enviada por correo, vigencia 15 días', $sentAt);

        // Qué pasó con la cotización: las viejas ya tienen respuesta del cliente
        $waitedDays = $sentAt->diffInDays($this->now);
        $acceptRate = 28 + (int) ($progress / 2); // mejora un poco con el tiempo
        $outcome = $waitedDays > 20
            ? $this->weighted([[OrderStatus::Accepted, $acceptRate], [OrderStatus::Rejected, 100 - $acceptRate - 15], [OrderStatus::Sent, 15]])
            : $this->weighted([[OrderStatus::Accepted, 25], [OrderStatus::Rejected, 10], [OrderStatus::Sent, 65]]);
        $decidedAt = $sentAt->addDays(mt_rand(1, 10))->addHours(mt_rand(0, 8));

        if ($outcome === OrderStatus::Sent || $decidedAt > $this->now) {
            return;
        }

        $quote->forceFill(['status' => $outcome])->save();
        $this->history($quote, OrderStatus::Sent, $outcome, $customer->user, $outcome === OrderStatus::Rejected ? 'Encontraron un mejor precio' : null, $decidedAt);

        if ($outcome === OrderStatus::Accepted) {
            $lines = $quote->load('items')->items->map(fn ($item) => [Product::find($item->product_id), (float) $item->quantity, (float) $item->unit_price])->all();
            $this->createPurchase($customer, $lines, $this->cap($decidedAt->addHours(mt_rand(1, 6))), $quote);
        }
    }

    /**
     * Pone precio a cada línea: el del catálogo o, si el producto es por
     * cotización, el costo más un margen de 25 % a 45 % con IVA.
     */
    private function priceQuote(Order $quote): void
    {
        $prices = [];

        foreach ($quote->load('items.product')->items as $item) {
            $product = $item->product;
            $prices[$item->id] = $product->price !== null
                ? (float) $product->price
                : round((float) ($product->cost_price ?? 900000) * (1 + mt_rand(25, 45) / 100) * (1 + (float) $product->tax_rate / 100), -3);
        }

        $shipping = $quote->delivery_method === DeliveryMethod::Pickup ? 0 : 35000;

        // Mismo camino que el panel: OrderService calcula líneas y totales
        $this->orderService->priceQuote($quote, $prices, $shipping);
    }

    // ---------------------------------------------------------------- utilidades

    private function newOrder(OrderType $type, OrderStatus $status, Customer $customer, CarbonImmutable $createdAt, array $extra): Order
    {
        $customer->loadMissing('user');

        $order = (new Order)->forceFill($extra + [
            'code' => $this->orders->nextCode($type),
            'type' => $type,
            'status' => $status,
            'customer_id' => $customer->id,
            // Copia de los datos del cliente
            'customer_type' => $customer->type,
            'company_name' => $customer->company_name,
            'nit' => $customer->nit,
            'contact_name' => $customer->contact_name,
            'email' => $customer->user->email,
            'phone' => $customer->phone,
            'city' => $customer->city,
            'address' => $customer->address,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        $order->save();

        return $order;
    }

    /** @param  list<array{0: Product, 1: float|int, 2: ?float}>  $lines */
    private function addLines(Order $order, array $lines): void
    {
        foreach ($lines as [$product, $quantity, $unitPrice]) {
            $order->items()->create([
                'product_id' => $product->id,
                // Copia de los datos del producto
                'sku' => $product->sku,
                'product_name' => $product->name,
                'unit' => $product->unit,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax_rate' => $product->tax_rate,
                'line_total' => $unitPrice === null ? null : round($unitPrice * $quantity, 2),
            ]);
        }
    }

    private function history(Order $order, ?OrderStatus $from, OrderStatus $to, ?User $user, ?string $note, CarbonImmutable $at): void
    {
        $order->statusHistories()->make([
            'user_id' => $user?->id,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
        ])->forceFill(['created_at' => $at, 'updated_at' => $at])->save();

        $order->forceFill(['status' => $to, 'updated_at' => $at])->save();
    }

    /** Pedidos o cotizaciones de un mes: más en meses fuertes, menos en diciembre, y un tramo del mes si está en curso. */
    private function monthlyCount(float $base, CarbonImmutable $month): int
    {
        $count = $base * self::SEASON[$month->month] + mt_rand(-20, 20) / 10;

        if ($month->isSameMonth($this->now)) {
            $count *= $this->now->day / $month->daysInMonth;
        }

        return max(3, (int) round($count));
    }

    /** Un momento laboral al azar dentro del mes (sin pasar de hoy). */
    private function randomMoment(CarbonImmutable $month): CarbonImmutable
    {
        $lastDay = $month->isSameMonth($this->now) ? $this->now->day : $month->daysInMonth;
        $moment = $month->setDay(mt_rand(1, max(1, $lastDay)))->setTime(mt_rand(8, 17), mt_rand(0, 59));

        // Sábados y domingos se mueven al viernes anterior (o al lunes si cae el día 1)
        $moment = match ($moment->dayOfWeekIso) {
            6 => $moment->subDay(),
            7 => $moment->day > 2 ? $moment->subDays(2) : $moment->addDay(),
            default => $moment,
        };

        return $this->cap($moment);
    }

    private function cap(CarbonImmutable $moment): CarbonImmutable
    {
        return $moment->greaterThan($this->now) ? $this->now : $moment;
    }

    private function randomCustomer(): Customer
    {
        return $this->weighted(array_map(null, $this->customers, $this->customerWeights));
    }

    /**
     * Elige un valor al azar con pesos.
     *
     * @template T
     *
     * @param  list<array{0: T, 1: int|float}>  $options  [valor, peso]
     * @return T
     */
    private function weighted(array $options): mixed
    {
        $total = array_sum(array_column($options, 1));
        $roll = mt_rand(1, max(1, (int) round($total)));

        foreach ($options as [$value, $weight]) {
            $roll -= $weight;

            if ($roll <= 0) {
                return $value;
            }
        }

        return $options[array_key_last($options)][0];
    }
}
