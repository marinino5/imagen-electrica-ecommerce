<?php

namespace App\Models;

use App\Enums\CustomerType;
use App\Enums\DeliveryMethod;
use App\Enums\IdType;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Support\Document;
use App\Support\TextCase;
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
    // Compra sin cuenta y facturación electrónica (ver docs/siigo-mapeo.md)
    'is_guest', 'needs_invoice', 'billing_person_type', 'billing_id_type', 'billing_identification', 'billing_check_digit', 'billing_first_name', 'billing_last_name',
    'billing_commercial_name', 'billing_vat_responsible', 'billing_fiscal_responsibilities', 'billing_email', 'billing_address', 'billing_country_code',
    'billing_state_code', 'billing_department', 'billing_city_code', 'billing_postal_code', 'billing_contact_first_name', 'billing_contact_last_name',
    'delivery_state_code', 'delivery_department', 'delivery_city_code', 'delivery_city', 'delivery_neighborhood',
    'delivery_road', 'delivery_number', 'delivery_complement', 'delivery_postal_code', 'delivery_notes', 'delivery_same_as_billing',
    'privacy_accepted_at', 'privacy_version',
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
            'billing_id_type' => IdType::class,
            'billing_vat_responsible' => 'boolean',
            'billing_fiscal_responsibilities' => 'array',
            'needs_invoice' => 'boolean',
            'is_guest' => 'boolean',
            'delivery_same_as_billing' => 'boolean',
            'privacy_accepted_at' => 'datetime',
        ];
    }

    /** Nombre para mostrar: la razón social, o el nombre de la persona natural. */
    protected function displayName(): Attribute
    {
        return Attribute::get(fn () => $this->company_name ?: $this->contact_name);
    }

    protected function contactName(): Attribute
    {
        return Attribute::set(fn (?string $v) => TextCase::person($v));
    }

    protected function billingFirstName(): Attribute
    {
        return Attribute::set(fn (?string $v) => TextCase::person($v));
    }

    protected function billingLastName(): Attribute
    {
        return Attribute::set(fn (?string $v) => TextCase::person($v));
    }

    protected function city(): Attribute
    {
        return Attribute::set(fn (?string $v) => TextCase::place($v));
    }

    /** Documento para el panel: el de la factura si pidió, y si no el de la ficha (ver App\Support\Document). */
    protected function document(): Attribute
    {
        return Attribute::get(fn () => $this->billing_identification
            ? Document::describe($this->billing_id_type, $this->billing_identification, $this->billing_check_digit, $this->customer_type?->value)
            : Document::describe(null, $this->nit, null, $this->customer_type?->value));
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
