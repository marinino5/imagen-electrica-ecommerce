<?php

namespace App\Http\Resources\V1;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Pedido o cotización del cliente. Los totales son nulos mientras una
 * cotización no tiene todos sus precios. Datos internos (comercial asignado,
 * costos) no salen.
 *
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'customer_type' => $this->customer_type->value,
            'company_name' => $this->company_name,
            'nit' => $this->nit,
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'city' => $this->city,
            'address' => $this->address,
            'delivery_method' => $this->delivery_method->value,
            'delivery_method_label' => $this->delivery_method->label(),
            'payment_method' => $this->payment_method,
            'subtotal' => $this->money($this->subtotal),
            'tax' => $this->money($this->tax),
            'shipping' => $this->money($this->shipping),
            'total' => $this->money($this->total),
            'notes' => $this->notes,
            'expires_at' => $this->expires_at,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            // Cotización de la que salió este pedido / pedido que salió de esta cotización
            'quote_code' => $this->whenLoaded('quote', fn () => $this->quote?->code),
            'generated_order_code' => $this->whenLoaded('generatedOrder', fn () => $this->generatedOrder?->code),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'status_history' => OrderStatusHistoryResource::collection($this->whenLoaded('statusHistories')),
        ];
    }

    private function money(mixed $value): ?float
    {
        return $value === null ? null : (float) $value;
    }
}
