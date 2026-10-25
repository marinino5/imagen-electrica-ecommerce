<?php

namespace App\Models;

use App\Enums\ProductUnit;
use App\Enums\SaleMode;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'category_id', 'brand_id', 'sku', 'name', 'slug', 'short_description',
    'description', 'image_url', 'specs', 'unit', 'price', 'tax_rate', 'stock',
    'sale_mode', 'is_active', 'is_featured',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'unit' => ProductUnit::class,
            'sale_mode' => SaleMode::class,
            'price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /** 1:N (inversa) */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
