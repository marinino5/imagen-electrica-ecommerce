<?php

namespace App\Http\Resources\V1;

use App\Enums\SaleMode;
use App\Models\ProductLine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Línea de producto (la ficha del catálogo). En el listado trae un resumen calculado con sus
 * referencias (rango de HP, desde $, stock); en el detalle además todas las referencias.
 *
 * @mixin ProductLine
 */
class LineResource extends JsonResource
{
    private bool $detail = false;

    /** Resource con todas las referencias (GET /lines/{slug}). */
    public static function detail(ProductLine $line): static
    {
        $resource = new static($line);
        $resource->detail = true;

        return $resource;
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $variants = $this->variants;
        $purchasable = $variants->filter(fn ($v) => $v->sale_mode === SaleMode::Direct && $v->price !== null);
        $hp = $variants->map(fn ($v) => $v->specs['potencia_hp'] ?? null)->filter(fn ($x) => $x !== null)->unique()->sort()->values();
        $rpm = $variants->map(fn ($v) => $v->specs['velocidad_rpm'] ?? null)->filter(fn ($x) => $x !== null)->unique()->sort()->values();
        $amp = $variants->flatMap(fn ($v) => self::numbers($v->specs['corriente_a'] ?? null));
        $family = $this->category?->parent;
        $buyable = $purchasable->filter(fn ($v) => $v->stock > 0);
        $single = $variants->count() === 1 && $buyable->count() === 1 ? $buyable->first() : null;

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description ?? $family?->description,
            'brand_label' => $this->brand_label,
            'brand' => $this->whenLoaded('brand', fn () => $this->brand?->only(['id', 'name', 'slug'])),
            'family' => $family?->only(['id', 'name', 'slug']),
            'category' => $this->category?->only(['id', 'name', 'slug']),
            // Clave de la imagen de la línea en la tienda (public/img/categorias/{clave}.webp)
            'image' => $this->image_key,
            // Foto propia de la línea (ruta pública en la tienda); nula hasta que se carga. Sin ella la tienda usa 'image'.
            'image_url' => $this->image_url,
            // Segunda vista de la línea (la tarjeta la muestra al pasar el mouse); nula si no hay
            'image_alt_url' => $this->image_alt_url,
            'spec_chips' => $this->spec_chips ?? [],
            'sale_mode' => $this->sale_mode->value,
            'sale_mode_label' => $this->sale_mode->label(),
            'is_featured' => $this->is_featured,
            'refs_count' => $variants->count(),
            'stock_total' => (int) $variants->sum('stock'),
            'price_from' => $purchasable->isEmpty() ? null : (float) $purchasable->min('price'),
            'hp_values' => $hp->all(),
            'hp_min' => $hp->first(),
            'hp_max' => $hp->last(),
            'rpm_values' => $rpm->all(),
            'amp_min' => $amp->isEmpty() ? null : $amp->min(),
            'amp_max' => $amp->isEmpty() ? null : $amp->max(),
            // Línea con una sola referencia comprable: el catálogo la agrega al carrito sin abrir la ficha
            // Compra en línea solo si alguna referencia con precio tiene stock; con precio pero sin stock: «Agotado · cotizar»
            'buyable' => $buyable->isNotEmpty(),
            // Alguna referencia con precio es de referencia (por confirmar con la empresa)
            'price_is_reference' => $purchasable->contains(fn ($v) => $v->price_source !== null),
            'quick_variant' => $single ? new VariantResource($single) : null,
            'variants' => $this->when($this->detail, fn () => VariantResource::collection($variants)),
        ];
    }

    /** "2.8-4" → [2.8, 4.0]; 12 → [12.0]. */
    private static function numbers(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        preg_match_all('/\d+(?:[.,]\d+)?/', (string) $value, $m);

        return array_map(fn ($n) => (float) str_replace(',', '.', $n), $m[0]);
    }
}
