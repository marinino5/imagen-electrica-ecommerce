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
            // Foto de la referencia: el fabricante usa la misma para toda la línea, así que casi siempre es la de la línea
            'image_url' => $this->image_url,
            'hp' => $specs['potencia_hp'] ?? null,
            'rpm' => $specs['velocidad_rpm'] ?? null,
            'amp' => $specs['corriente_a'] ?? null,
            'volt' => $specs['voltaje_v'] ?? null,
            'ip' => $specs['proteccion_ip'] ?? null,
            // Los demás datos de la referencia (pines, potencia_w, longitud_m…): la tienda los muestra con su unidad y oculta los vacíos
            'specs' => (object) array_diff_key($specs, array_flip(['potencia_hp', 'velocidad_rpm', 'corriente_a', 'voltaje_v', 'proteccion_ip'])),
            'unit' => $this->unit->value,
            'unit_label' => $this->unit->label(),
            // Sin precio (null) la referencia solo se vende por cotización
            'price' => $this->price === null ? null : (float) $this->price,
            'tax_rate' => (float) $this->tax_rate,
            'stock' => $this->stock,
            'in_stock' => $this->stock > 0,
            'sale_mode' => $this->sale_mode->value,
            // «referencia» = precio de referencia, sujeto a confirmación por un asesor; nulo = precio confirmado
            'price_source' => $this->price_source?->value,
            // «Sin marca» (referencias del inventario sin fabricante conocido) no se muestra como marca
            'brand' => $this->whenLoaded('brand', fn () => $this->brand && $this->brand->slug !== 'sin-marca' ? $this->brand->only(['id', 'name', 'slug']) : null),
        ];
    }
}
