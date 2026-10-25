<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Exceptions\BusinessRuleException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
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

    private function describe(Order $order): string
    {
        return mb_strtolower($order->type->label()).' '.$order->code;
    }
}
