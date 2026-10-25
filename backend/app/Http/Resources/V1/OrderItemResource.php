<?php

namespace App\Http\Resources\V1;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Línea de pedido con la copia del producto (sku, nombre, unidad). Los
 * precios son nulos mientras una cotización no ha sido valorada.
 *
 * @mixin OrderItem
 */
class OrderItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'product_name' => $this->product_name,
            'unit' => $this->unit->value,
            'quantity' => (float) $this->quantity,
            'unit_price' => $this->unit_price === null ? null : (float) $this->unit_price,
            'tax_rate' => (float) $this->tax_rate,
            'line_total' => $this->line_total === null ? null : (float) $this->line_total,
            'notes' => $this->notes,
        ];
    }
}
