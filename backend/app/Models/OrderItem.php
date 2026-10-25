<?php

namespace App\Models;

use App\Enums\ProductUnit;
use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Línea de pedido: pivote entre orders y products con datos propios.
 * Guarda copia de sku, nombre y unidad para que el historial no cambie
 * si el producto se modifica o se elimina.
 */
#[Fillable([
    'order_id', 'product_id', 'sku', 'product_name', 'unit', 'quantity',
    'unit_price', 'tax_rate', 'line_total', 'notes',
])]
class OrderItem extends Pivot
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    /** Columnas extra que se leen desde $order->products y $product->orders */
    public const PIVOT_COLUMNS = [
        'id', 'sku', 'product_name', 'unit', 'quantity',
        'unit_price', 'tax_rate', 'line_total', 'notes',
    ];

    protected $table = 'order_items';

    // Los pivotes no tienen id por defecto; esta tabla sí.
    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'unit' => ProductUnit::class,
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Incluye productos eliminados (soft delete) para mostrar el historial. */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
