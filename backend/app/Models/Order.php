<?php

namespace App\Models;

use App\Enums\CustomerType;
use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Pedido (type = purchase) o cotización (type = quote).
 */
#[Fillable([
    'code', 'type', 'status', 'customer_id', 'assigned_to', 'quote_id',
    'customer_type', 'company_name', 'nit', 'contact_name', 'email', 'phone', 'city', 'address',
    'delivery_method', 'payment_method', 'subtotal', 'tax', 'shipping', 'total',
    'notes', 'expires_at', 'paid_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $attributes = ['customer_type' => 'company'];

    protected function casts(): array
    {
        return [
            'type' => OrderType::class,
            'status' => OrderStatus::class,
            'customer_type' => CustomerType::class,
            'delivery_method' => DeliveryMethod::class,
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /** Nombre para mostrar: la razón social, o el nombre de la persona natural. */
    protected function displayName(): Attribute
    {
        return Attribute::get(fn () => $this->company_name ?: $this->contact_name);
    }

    /** 1:N (inversa) */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** Comercial responsable. 1:N (inversa) */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** Cotización de la que salió este pedido. */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'quote_id');
    }

    /** Pedido generado al aceptar esta cotización. */
    public function generatedOrder(): HasOne
    {
        return $this->hasOne(Order::class, 'quote_id');
    }

    /** Líneas del pedido. 1:N */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Productos del pedido. N:M con pivote order_items */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->using(OrderItem::class)
            ->withPivot(OrderItem::PIVOT_COLUMNS)
            ->withTimestamps();
    }

    /** Historial de estados, del más antiguo al más reciente. 1:N */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->oldest()->oldest('id');
    }
}
