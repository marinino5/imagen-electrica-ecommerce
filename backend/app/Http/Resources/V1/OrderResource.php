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
            'is_guest' => $this->is_guest,
            // Datos de facturación electrónica (nulos en pedidos anteriores a la compra sin cuenta)
            'billing' => $this->billing_doc_type === null ? null : [
                'doc_type' => $this->billing_doc_type->value,
                'doc_type_label' => $this->billing_doc_type->label(),
                'doc_number' => $this->billing_doc_number,
                'doc_dv' => $this->billing_doc_dv,
                'first_name' => $this->billing_first_name,
                'last_name' => $this->billing_last_name,
                'fiscal_responsibility' => $this->fiscal_responsibility?->value,
                'fiscal_responsibility_label' => $this->fiscal_responsibility?->label(),
                'email' => $this->billing_email,
                'address' => $this->billing_address,
                'department_code' => $this->billing_department_code,
                'department' => $this->billing_department,
                'city_code' => $this->billing_city_code,
            ],
            // Entrega en columnas separadas (nulo al recoger en tienda)
            'delivery' => $this->delivery_city_code === null ? null : [
                'department_code' => $this->delivery_department_code,
                'department' => $this->delivery_department,
                'city_code' => $this->delivery_city_code,
                'city' => $this->delivery_city,
                'neighborhood' => $this->delivery_neighborhood,
                'road' => $this->delivery_road,
                'number' => $this->delivery_number,
                'complement' => $this->delivery_complement,
                'postal_code' => $this->delivery_postal_code,
                'notes' => $this->delivery_notes,
                'same_as_billing' => $this->delivery_same_as_billing,
            ],
            'privacy_accepted_at' => $this->privacy_accepted_at,
            'privacy_version' => $this->privacy_version,
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
