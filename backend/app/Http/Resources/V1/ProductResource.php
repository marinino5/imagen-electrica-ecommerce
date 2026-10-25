<?php

namespace App\Http\Resources\V1;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Producto del catálogo. Los precios incluyen IVA. El costo de compra
 * (cost_price) nunca sale: el modelo lo mantiene oculto.
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'specs' => $this->specs,
            'unit' => $this->unit->value,
            'unit_label' => $this->unit->label(),
            // Sin precio (null) el producto solo se vende por cotización
            'price' => $this->price === null ? null : (float) $this->price,
            'tax_rate' => (float) $this->tax_rate,
            'stock' => $this->stock,
            'in_stock' => $this->stock > 0,
            'sale_mode' => $this->sale_mode->value,
            'sale_mode_label' => $this->sale_mode->label(),
            'is_featured' => $this->is_featured,
            'category' => $this->whenLoaded('category', fn () => $this->category->only(['id', 'name', 'slug'])),
            'brand' => $this->whenLoaded('brand', fn () => $this->brand->only(['id', 'name', 'slug', 'logo_url'])),
        ];
    }
}
