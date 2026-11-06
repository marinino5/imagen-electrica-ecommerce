<?php

namespace App\Http\Resources\V1;

use App\Enums\SaleMode;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Promoción vigente. Calcula el precio de lista, el precio promocional y los datos mínimos de la línea
 * para pintar la tarjeta y enlazar a la ficha.
 *
 * @mixin Promotion
 */
class PromotionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $line = $this->line;
        $base = $this->basePrice();
        $final = $this->special_price !== null
            ? (float) $this->special_price
            : ($base !== null && $this->discount_percent !== null ? round($base * (100 - (float) $this->discount_percent) / 100) : null);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'discount_percent' => $this->discount_percent !== null ? (float) $this->discount_percent : null,
            'special_price' => $this->special_price !== null ? (float) $this->special_price : null,
            'original_price' => $base,
            'final_price' => $final,
            // Precio "desde": la promoción cubre toda la línea y no una sola referencia
            'is_from_price' => $this->product === null && $line->variants->count() > 1,
            'starts_at' => $this->starts_at->toDateString(),
            'ends_at' => $this->ends_at->toDateString(),
            'sku' => $this->product?->sku,
            'line' => [
                'slug' => $line->slug,
                'name' => $line->name,
                'brand_label' => $line->brand_label,
                'family' => $line->category?->parent?->only(['name', 'slug']),
                'category' => $line->category?->only(['name', 'slug']),
                'image' => $line->image_key,
                'image_url' => $line->image_url,
            ],
        ];
    }

    /** Precio de lista con IVA: el de la referencia, o el más bajo de las comprables de la línea. */
    private function basePrice(): ?float
    {
        if ($this->product) {
            return $this->product->price !== null ? (float) $this->product->price : null;
        }

        $prices = $this->line->variants
            ->filter(fn ($v) => $v->sale_mode === SaleMode::Direct && $v->price !== null)
            ->map(fn ($v) => (float) $v->price);

        return $prices->isEmpty() ? null : $prices->min();
    }
}
