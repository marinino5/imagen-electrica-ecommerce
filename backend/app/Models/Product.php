<?php

namespace App\Models;

use App\Enums\PriceSource;
use App\Enums\ProductUnit;
use App\Enums\SaleMode;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'category_id', 'brand_id', 'line_id', 'sku', 'name', 'variant_label', 'price_is_simulated', 'price_source', 'slug', 'short_description',
    'description', 'image_url', 'specs', 'unit', 'price', 'cost_price', 'tax_rate', 'stock',
    'sale_mode', 'is_active', 'is_featured',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * El costo es un dato interno: oculto para que nunca salga al convertir el
     * modelo a array o JSON (API Resources, respuestas de la tienda).
     *
     * @var list<string>
     */
    protected $hidden = ['cost_price'];

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'unit' => ProductUnit::class,
            'sale_mode' => SaleMode::class,
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'price_is_simulated' => 'boolean',
            'price_source' => PriceSource::class,
        ];
    }

    /** 1:N (inversa) */
    /**
     * Foto lista para mostrar en el panel. Las fotos propias del catálogo se guardan como ruta («/img/catalogo/…») y viven en la tienda,
     * no en el servidor del panel; por eso se les antepone la dirección de la tienda. Las URL completas se dejan como están.
     */
    protected function imageSrc(): Attribute
    {
        return Attribute::get(fn () => $this->image_url && str_starts_with($this->image_url, '/')
            ? config('shop.frontend_url').$this->image_url
            : $this->image_url);
    }

    /** Se puede comprar ahora: con el stock apagado (antes de Siigo) siempre; con el stock activo, solo si hay unidades. */
    public function hasAvailability(): bool
    {
        return ! config('shop.track_stock') || $this->stock > 0;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Línea (ficha) a la que pertenece esta referencia; nulo en productos sueltos */
    public function line(): BelongsTo
    {
        return $this->belongsTo(ProductLine::class, 'line_id');
    }

    /** 1:N (inversa) */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** Líneas de pedido donde aparece el producto. 1:N */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Pedidos y cotizaciones que incluyen el producto. N:M con pivote order_items */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->using(OrderItem::class)
            ->withPivot(OrderItem::PIVOT_COLUMNS)
            ->withTimestamps();
    }
}
