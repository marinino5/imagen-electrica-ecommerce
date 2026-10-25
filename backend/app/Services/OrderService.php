<?php

namespace App\Services;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\ProductUnit;
use App\Enums\SaleMode;
use App\Exceptions\BusinessRuleException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Reglas de negocio de pedidos y cotizaciones: transiciones de estado con
 * historial, valoración de cotizaciones, totales y generación del pedido
 * cuando se acepta una cotización.
 */
class OrderService
{
    public const PER_PAGE = 10;

    /** Solicitudes por página en «Mis pedidos» de la API (y máximo que se puede pedir). */
    public const CUSTOMER_PER_PAGE = 10;

    public const CUSTOMER_MAX_PER_PAGE = 50;

    /** Horas que puede esperar una cotización antes de considerarse atrasada. */
    public const QUOTE_RESPONSE_HOURS = 24;

    /** Estados en los que el cliente todavía no ha recibido respuesta. */
    public const UNANSWERED_QUOTE_STATUSES = [OrderStatus::New, OrderStatus::InReview];

    /**
     * Transiciones permitidas: tipo => estado actual => estados siguientes.
     * Todo lo que no esté aquí se rechaza (los estados finales no tienen salida).
     *
     * @var array<string, array<string, array<int, OrderStatus>>>
     */
    private const TRANSITIONS = [
        'purchase' => [
            'pending_payment' => [OrderStatus::Paid, OrderStatus::Cancelled],
            'paid' => [OrderStatus::Shipped],
            'shipped' => [OrderStatus::Delivered],
        ],
        'quote' => [
            'new' => [OrderStatus::InReview, OrderStatus::Cancelled],
            'in_review' => [OrderStatus::Sent],
            'sent' => [OrderStatus::Accepted, OrderStatus::Rejected],
        ],
    ];

    public function __construct(
        private readonly OrderRepository $orders,
        private readonly ProductRepository $products,
        private readonly UserRepository $users,
        private readonly InventoryService $inventory,
    ) {}

    /**
     * @param  array{type?: ?string, status?: array<int, string>, overdue?: bool, q?: ?string}  $filters
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->orders->paginateFiltered($filters, $this->overdueCutoff(), self::UNANSWERED_QUOTE_STATUSES, self::PER_PAGE);
    }

    /**
     * @param  array{type?: ?string, status?: ?string}  $filters
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginateForCustomer(Customer $customer, array $filters, int $perPage = self::CUSTOMER_PER_PAGE): LengthAwarePaginator
    {
        return $this->orders->paginateForCustomer($customer->id, $filters, $perPage);
    }

    /**
     * Detalle de una solicitud del cliente. Si el código no existe o es de otro
     * cliente responde igual (404): no se revela que existe.
     *
     * @throws ModelNotFoundException
     */
    public function findForCustomer(Customer $customer, string $code): Order
    {
        return $this->details($this->orders->findForCustomerByCodeOrFail($customer->id, $code));
    }

    public function details(Order $order): Order
    {
        return $this->orders->loadDetails($order);
    }

    /** @return Collection<int, User> */
    public function commercials(): Collection
    {
        return $this->users->activeCommercials();
    }

    /** Una cotización sin respuesta creada antes de esta fecha está atrasada. */
    public function overdueCutoff(): CarbonImmutable
    {
        return CarbonImmutable::now()->subHours(self::QUOTE_RESPONSE_HOURS);
    }

    /** Cotización sin responder desde hace más de QUOTE_RESPONSE_HOURS horas. */
    public function isOverdue(Order $order): bool
    {
        return $order->type === OrderType::Quote
            && in_array($order->status, self::UNANSWERED_QUOTE_STATUSES, true)
            && $order->created_at->lt($this->overdueCutoff());
    }

    /** @return array<int, OrderStatus> */
    public function allowedTransitions(Order $order): array
    {
        return self::TRANSITIONS[$order->type->value][$order->status->value] ?? [];
    }

    /** Solo se ponen precios mientras la cotización no se ha enviado al cliente. */
    public function canPrice(Order $order): bool
    {
        return $order->type === OrderType::Quote && in_array($order->status, self::UNANSWERED_QUOTE_STATUSES, true);
    }

    /** Líneas de la cotización a las que todavía les falta precio. */
    public function missingPrices(Order $order): int
    {
        return $order->items->whereNull('unit_price')->count();
    }

    /**
     * Cambia el estado validando la transición y guarda el historial.
     * Todo ocurre en una transacción: si algo falla no queda nada a medias.
     *
     * - Una cotización no pasa a "sent" si le falta algún precio.
     * - Al confirmar el pago de un pedido se valida y descuenta el stock.
     * - Al aceptar una cotización se genera un pedido nuevo (con quote_id).
     *
     * @throws BusinessRuleException
     */
    public function changeStatus(Order $order, OrderStatus $to, ?User $by = null, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $to, $by, $note) {
            // Se relee con bloqueo: dos personas no pueden cambiar la misma solicitud a la vez
            $order = $this->orders->lockForUpdate($order);
            $from = $order->status;

            if (! in_array($to, $this->allowedTransitions($order), true)) {
                throw new BusinessRuleException(
                    "No se puede pasar {$this->describe($order)} de «{$from->label()}» a «{$to->label()}»."
                );
            }

            $changes = ['status' => $to];

            if ($order->type === OrderType::Quote && $to === OrderStatus::Sent) {
                $missing = $this->missingPrices($order->load('items'));

                if ($missing > 0) {
                    throw new BusinessRuleException(
                        "No se puede enviar la cotización {$order->code}: faltan precios en {$missing} ".($missing === 1 ? 'producto.' : 'productos.')
                    );
                }
            }

            if ($order->type === OrderType::Purchase && $to === OrderStatus::Paid) {
                $this->inventory->deductForOrder($order);
                $changes['paid_at'] = now();
            }

            $this->orders->update($order, $changes);
            $this->orders->addHistory($order, $from, $to, $by?->id, $note);

            if ($order->type === OrderType::Quote && $to === OrderStatus::Accepted) {
                $this->createPurchaseFromQuote($order);
            }

            return $order;
        });
    }

    /**
     * Cancelar no borra: es un cambio de estado con historial. El cliente solo
     * puede cancelar lo que sigue sin gestionar (pedido pendiente de pago o
     * cotización nueva); lo demás lo rechaza changeStatus con 422.
     *
     * @throws BusinessRuleException
     */
    public function cancelForCustomer(Customer $customer, string $code): Order
    {
        $order = $this->orders->findForCustomerByCodeOrFail($customer->id, $code);

        $this->changeStatus($order, OrderStatus::Cancelled, $customer->user, 'Cancelado por el cliente');

        return $this->details($order->refresh());
    }

    /**
     * Convierte el carrito en solicitudes. Regla de clasificación:
     * - Pedido (purchase): productos de venta directa, con precio y stock suficiente.
     * - Cotización (quote): productos de cotización o bajo pedido, o sin stock suficiente.
     * Un carrito mixto genera las dos. No se descuenta stock: eso ocurre al confirmar el pago.
     *
     * Las cotizaciones nacen sin precios (las valora el comercial); el pedido
     * se valora con los precios actuales y el envío configurado en config/shop.php.
     *
     * @param  array{items: array<int, array{product_id: int, quantity: float|int|string, notes?: ?string}>, delivery_method: string, address?: ?string, notes?: ?string}  $data
     * @return Collection<int, Order> pedido primero y cotización después, solo los que apliquen
     *
     * @throws BusinessRuleException
     */
    public function createFromCart(Customer $customer, array $data): Collection
    {
        return DB::transaction(function () use ($customer, $data) {
            $customer->loadMissing('user');
            $lines = $this->cartLines($data['items']);

            $purchaseLines = array_values(array_filter($lines, fn (array $line) => $this->isDirectPurchase($line['product'], $line['quantity'])));
            $quoteLines = array_values(array_filter($lines, fn (array $line) => ! $this->isDirectPurchase($line['product'], $line['quantity'])));

            $orders = new Collection;

            foreach ([[OrderType::Purchase, $purchaseLines], [OrderType::Quote, $quoteLines]] as [$type, $typeLines]) {
                if ($typeLines !== []) {
                    $orders->push($this->details($this->createRequest($type, $customer, $data, $typeLines)));
                }
            }

            return $orders;
        });
    }

    /**
     * Costo de envío: gratis al recoger o al llegar a la compra mínima; si no,
     * el valor configurado para la modalidad.
     */
    public function shippingCost(DeliveryMethod $method, float $linesTotal): float
    {
        if ($method === DeliveryMethod::Pickup || $linesTotal >= (float) config('shop.shipping.free_shipping_from')) {
            return 0.0;
        }

        return round((float) config("shop.shipping.cost.{$method->value}", 0), 2);
    }

    /**
     * Asigna (o quita, con null) el comercial responsable.
     *
     * @throws BusinessRuleException si el usuario no es un comercial activo
     */
    public function assign(Order $order, ?int $userId): Order
    {
        if ($userId !== null && ! $this->users->findActiveCommercial($userId)) {
            throw new BusinessRuleException('Solo se puede asignar a un comercial activo.');
        }

        return $this->orders->update($order, ['assigned_to' => $userId]);
    }

    /**
     * Pone el precio de cada línea de una cotización (precio unitario con IVA
     * incluido, por id de línea) y recalcula los totales. Una línea con precio
     * vacío queda sin valorar; los totales solo existen cuando todas tienen precio.
     *
     * @param  array<int|string, float|int|string|null>  $unitPrices  id de línea => precio unitario
     *
     * @throws BusinessRuleException si no es una cotización que se pueda valorar
     */
    public function priceQuote(Order $quote, array $unitPrices, float|int|string|null $shipping = null): Order
    {
        return DB::transaction(function () use ($quote, $unitPrices, $shipping) {
            $quote = $this->orders->lockForUpdate($quote)->load('items');

            if (! $this->canPrice($quote)) {
                throw new BusinessRuleException('Solo se pueden poner precios a una cotización que todavía no se ha enviado al cliente.');
            }

            foreach ($quote->items as $item) {
                if (! array_key_exists($item->id, $unitPrices)) {
                    continue;
                }

                $price = $unitPrices[$item->id] === null || $unitPrices[$item->id] === '' ? null : round((float) $unitPrices[$item->id], 2);

                $this->orders->updateItem($item, [
                    'unit_price' => $price,
                    'line_total' => $price === null ? null : round($price * (float) $item->quantity, 2),
                ]);
            }

            if ($shipping !== null && $shipping !== '') {
                $this->orders->update($quote, ['shipping' => round((float) $shipping, 2)]);
            }

            return $this->recalculateTotals($quote->refresh()->load('items'));
        });
    }

    /**
     * Los precios incluyen IVA: la base de cada línea es total / (1 + tasa)
     * y el IVA es lo que sobra. Sin todos los precios los totales quedan nulos.
     */
    public function recalculateTotals(Order $order): Order
    {
        if ($order->items->isEmpty() || $order->items->contains(fn (OrderItem $item) => $item->line_total === null)) {
            return $this->orders->update($order, ['subtotal' => null, 'tax' => null, 'total' => null]);
        }

        $lines = round($order->items->sum(fn (OrderItem $item) => (float) $item->line_total), 2);
        $base = round($order->items->sum(fn (OrderItem $item) => (float) $item->line_total / (1 + (float) $item->tax_rate / 100)), 2);
        $shipping = (float) ($order->shipping ?? 0);

        return $this->orders->update($order, [
            'subtotal' => $base,
            'tax' => round($lines - $base, 2),
            'shipping' => $shipping,
            'total' => round($lines + $shipping, 2),
        ]);
    }

    /**
     * Pedido nuevo con los datos y líneas de la cotización aceptada. La
     * cotización se conserva; el pedido nace pendiente de pago.
     */
    private function createPurchaseFromQuote(Order $quote): Order
    {
        $quote->load('items');

        $purchase = $this->orders->create([
            'code' => $this->orders->nextCode(OrderType::Purchase),
            'type' => OrderType::Purchase,
            'status' => OrderStatus::initialFor(OrderType::Purchase),
            'quote_id' => $quote->id,
            'customer_id' => $quote->customer_id,
            'assigned_to' => $quote->assigned_to,
            // Copia de los datos del cliente
            'company_name' => $quote->company_name,
            'nit' => $quote->nit,
            'contact_name' => $quote->contact_name,
            'email' => $quote->email,
            'phone' => $quote->phone,
            'city' => $quote->city,
            'address' => $quote->address,
            'delivery_method' => $quote->delivery_method,
            'subtotal' => $quote->subtotal,
            'tax' => $quote->tax,
            'shipping' => $quote->shipping,
            'total' => $quote->total,
            'notes' => $quote->notes,
        ]);

        foreach ($quote->items as $item) {
            $this->orders->addItem($purchase, $item->only([
                'product_id', 'sku', 'product_name', 'unit', 'quantity',
                'unit_price', 'tax_rate', 'line_total', 'notes',
            ]));
        }

        $this->orders->addHistory($purchase, null, $purchase->status, null, "Generado al aceptar la cotización {$quote->code}");

        return $purchase;
    }

    /**
     * Une cada línea del carrito con su producto y valida la cantidad.
     *
     * @param  array<int, array{product_id: int, quantity: float|int|string, notes?: ?string}>  $items
     * @return array<int, array{product: Product, quantity: float, notes: ?string}>
     *
     * @throws BusinessRuleException si un producto ya no está disponible o la cantidad no corresponde a su unidad
     */
    private function cartLines(array $items): array
    {
        $products = $this->products->findActiveByIds(array_column($items, 'product_id'));
        $lines = [];

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);

            if (! $product) {
                throw new BusinessRuleException('Uno de los productos del carrito ya no está disponible.');
            }

            $quantity = round((float) $item['quantity'], 2);

            // Solo el cable se vende en fracciones (por metro)
            if ($product->unit !== ProductUnit::Metro && floor($quantity) !== $quantity) {
                throw new BusinessRuleException("«{$product->name}» se vende por {$product->unit->value}: la cantidad debe ser un número entero.");
            }

            $lines[] = ['product' => $product, 'quantity' => $quantity, 'notes' => $item['notes'] ?? null];
        }

        return $lines;
    }

    private function isDirectPurchase(Product $product, float $quantity): bool
    {
        return $product->sale_mode === SaleMode::Direct
            && $product->price !== null
            && $this->inventory->canFulfill($product, $quantity);
    }

    /**
     * Crea una solicitud con sus líneas, copiando los datos del cliente y del
     * producto, y deja el primer estado en el historial.
     *
     * @param  array<int, array{product: Product, quantity: float, notes: ?string}>  $lines
     */
    private function createRequest(OrderType $type, Customer $customer, array $data, array $lines): Order
    {
        $isPurchase = $type === OrderType::Purchase;
        $method = DeliveryMethod::from($data['delivery_method']);
        $status = OrderStatus::initialFor($type);

        $linesTotal = $isPurchase
            ? round(array_sum(array_map(fn (array $line) => (float) $line['product']->price * $line['quantity'], $lines)), 2)
            : 0.0;

        $order = $this->orders->create([
            'code' => $this->orders->nextCode($type),
            'type' => $type,
            'status' => $status,
            'customer_id' => $customer->id,
            // Copia de los datos del cliente
            'company_name' => $customer->company_name,
            'nit' => $customer->nit,
            'contact_name' => $customer->contact_name,
            'email' => $customer->user->email,
            'phone' => $customer->phone,
            'city' => $customer->city,
            'address' => ($data['address'] ?? null) ?: $customer->address,
            'delivery_method' => $method,
            // Una cotización no tiene envío hasta que el comercial la valore
            'shipping' => $isPurchase ? $this->shippingCost($method, $linesTotal) : null,
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($lines as $line) {
            /** @var Product $product */
            $product = $line['product'];
            $unitPrice = $isPurchase ? (float) $product->price : null;

            $this->orders->addItem($order, [
                'product_id' => $product->id,
                // Copia de los datos del producto
                'sku' => $product->sku,
                'product_name' => $product->name,
                'unit' => $product->unit,
                'quantity' => $line['quantity'],
                'unit_price' => $unitPrice,
                'tax_rate' => $product->tax_rate,
                'line_total' => $unitPrice === null ? null : round($unitPrice * $line['quantity'], 2),
                'notes' => $line['notes'],
            ]);
        }

        if ($isPurchase) {
            $this->recalculateTotals($order->load('items'));
        }

        $this->orders->addHistory($order, null, $status, $customer->user_id, null);

        return $order;
    }

    private function describe(Order $order): string
    {
        return mb_strtolower($order->type->label()).' '.$order->code;
    }
}
