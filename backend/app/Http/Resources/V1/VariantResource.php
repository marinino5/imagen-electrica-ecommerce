<?php

namespace App\Http\Resources\V1;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Referencia (SKU) de una línea. Tiene la misma forma que ProductResource en lo que usa el carrito
 * (id, sku, name, slug, price, tax_rate, stock, sale_mode, unit, brand), más los datos de la placa.
 *
 * @mixin Product
 */
class VariantResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $specs = $this->specs ?? [];

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'label' => $this->variant_label,
            'hp' => $specs['potencia_hp'] ?? null,
            'rpm' => $specs['velocidad_rpm'] ?? null,
            'amp' => $specs['corriente_a'] ?? null,
            'volt' => $specs['voltaje_v'] ?? null,
            'ip' => $specs['proteccion_ip'] ?? null,
            'unit' => $this->unit->value,
            'unit_label' => $this->unit->label(),
            // Sin precio (null) la referencia solo se vende por cotización
            'price' => $this->price === null ? null : (float) $this->price,
            'tax_rate' => (float) $this->tax_rate,
            'stock' => $this->stock,
            'in_stock' => $this->stock > 0,
            'sale_mode' => $this->sale_mode->value,
            'brand' => $this->whenLoaded('brand', fn () => $this->brand?->only(['id', 'name', 'slug'])),
        ];
    }
}
