<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Repositories\Concerns\EscapesLike;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository
{
    use EscapesLike;

    /**
     * Listado del panel con filtros. Claves de $filters (todas opcionales):
     * type, status (lista de estados), overdue (cotizaciones sin responder
     * creadas antes de $overdueBefore) y q (código, empresa, NIT o contacto).
     *
     * @param  array{type?: ?string, status?: array<int, string>, overdue?: bool, q?: ?string}  $filters
     * @param  array<int, OrderStatus>  $unansweredStatuses
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginateFiltered(array $filters, CarbonInterface $overdueBefore, array $unansweredStatuses, int $perPage): LengthAwarePaginator
    {
        return Order::query()
            ->with('assignee:id,name')
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['status'] ?? [], fn (Builder $query, array $statuses) => $query->whereIn('status', $statuses))
            ->when($filters['overdue'] ?? false, fn (Builder $query) => $query
                ->where('type', OrderType::Quote)
                ->whereIn('status', $unansweredStatuses)
                ->where('created_at', '<', $overdueBefore))
            ->when($filters['q'] ?? null, function (Builder $query, string $term) {
                $like = $this->likePattern($term);
                $query->where(fn ($q) => $q
                    ->where('code', 'like', $like)
                    ->orWhere('company_name', 'like', $like)
                    ->orWhere('nit', 'like', $like)
                    ->orWhere('contact_name', 'like', $like));
            })
            ->latest()
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Pedidos y cotizaciones de un cliente, los más recientes primero.
     * Claves de $filters (opcionales): type y status.
     *
     * @param  array{type?: ?string, status?: ?string}  $filters
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginateForCustomer(int $customerId, array $filters, int $perPage): LengthAwarePaginator
    {
        return Order::query()
            ->with('items')
            ->where('customer_id', $customerId)
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Una solicitud por código, solo si es del cliente (si no, 404). */
    public function findForCustomerByCodeOrFail(int $customerId, string $code): Order
    {
        return Order::query()->where('customer_id', $customerId)->where('code', $code)->firstOrFail();
    }

    public function loadDetails(Order $order): Order
    {
        return $order->load([
            'items.product:id,name,slug',
            'statusHistories.user:id,name',
            'assignee:id,name',
            'quote:id,code',
            'generatedOrder:id,code,quote_id',
        ]);
    }

    /** Vuelve a leer la solicitud bloqueándola hasta el final de la transacción. */
    public function lockForUpdate(Order $order): Order
    {
        return Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order;
    }

    public function addItem(Order $order, array $data): OrderItem
    {
        return $order->items()->create($data);
    }

    public function updateItem(OrderItem $item, array $data): OrderItem
    {
        $item->update($data);

        return $item;
    }

    public function addHistory(Order $order, ?OrderStatus $from, OrderStatus $to, ?int $userId, ?string $note): OrderStatusHistory
    {
        return $order->statusHistories()->create([
            'user_id' => $userId,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
        ]);
    }

    /** Siguiente código libre del tipo: PED-0001, PED-0002... / COT-0001... */
    public function nextCode(OrderType $type): string
    {
        // Bloquea la última fila: dos solicitudes simultáneas no reciben el mismo código
        $last = Order::query()->where('type', $type)->latest('id')->lockForUpdate()->value('code');
        $number = $last ? (int) substr($last, strlen($type->codePrefix()) + 1) : 0;

        return sprintf('%s-%04d', $type->codePrefix(), $number + 1);
    }
}
