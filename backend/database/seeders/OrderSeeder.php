<?php

namespace Database\Seeders;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Tres solicitudes de ejemplo, una por situación típica:
 * PED-0001 pedido pagado, COT-0001 cotización en revisión sin precios,
 * COT-0002 cotización enviada con precios.
 *
 * Los totales se calculan aquí solo para tener datos de prueba; la regla
 * oficial vivirá en OrderService.
 *
 * Idempotente: cada solicitud se busca por su code; si ya existe no se vuelve
 * a crear ni se le duplican ítems ni historial. Todo va en una transacción
 * para no dejar una solicitud a medias.
 */
class OrderSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(fn () => $this->seedOrders());
    }

    private function seedOrders(): void
    {
        $comercial = User::where('role', UserRole::Comercial)->firstOrFail();
        [$metalmecanica, $constructora] = Customer::with('user')->orderBy('id')->get();

        // PED-0001: compra directa, pagada por transferencia
        $order = $this->createOrder('PED-0001', OrderType::Purchase, OrderStatus::Paid, $metalmecanica, [
            'delivery_method' => DeliveryMethod::Pickup,
            'payment_method' => 'transferencia',
            'paid_at' => now()->subDays(2),
        ], [
            ['00118ET3E143T-W22', 2],
            ['NEX-THHN-12AWG', 50.5],
            ['3RV2011-1KA10', 2],
        ], shipping: 0);
        $this->history($order, [
            [null, OrderStatus::PendingPayment, $metalmecanica->user, 'Pedido creado desde la tienda', now()->subDays(3)],
            [OrderStatus::PendingPayment, OrderStatus::Paid, $comercial, 'Pago confirmado por transferencia', now()->subDays(2)],
        ]);

        // COT-0001: productos de cotización y bajo pedido, todavía sin precios
        $quote = $this->createOrder('COT-0001', OrderType::Quote, OrderStatus::InReview, $constructora, [
            'delivery_method' => DeliveryMethod::National,
            'assigned_to' => $comercial->id,
            'notes' => 'Entrega en la obra. Requieren factura a nombre de la constructora.',
        ], [
            ['01018ET3E215T-W22', 1],
            ['SSW070030T5SZ', 1],
        ]);
        $this->history($quote, [
            [null, OrderStatus::New, $constructora->user, null, now()->subDay()],
            [OrderStatus::New, OrderStatus::InReview, $comercial, 'Consultando tiempo de importación del arrancador', now()->subHours(5)],
        ]);

        // COT-0002: cotización valorada por el comercial y enviada al cliente
        $sent = $this->createOrder('COT-0002', OrderType::Quote, OrderStatus::Sent, $metalmecanica, [
            'delivery_method' => DeliveryMethod::Local,
            'assigned_to' => $comercial->id,
            'expires_at' => now()->addDays(15),
        ], [
            ['CES-SC-1HP-1-30', 2, 1250000],
            ['HCT-45-4T', 1, 2450000],
        ], shipping: 35000);
        $this->history($sent, [
            [null, OrderStatus::New, $metalmecanica->user, null, now()->subDays(4)],
            [OrderStatus::New, OrderStatus::InReview, $comercial, null, now()->subDays(3)],
            [OrderStatus::InReview, OrderStatus::Sent, $comercial, 'Cotización enviada por correo, vigencia 15 días', now()->subDays(1)],
        ]);
    }

    /**
     * @param  array<int, array{0: string, 1: float|int, 2?: int}>  $lines  [sku, cantidad, precio opcional]
     * @param  float|null  $shipping  Nulo si la solicitud no tiene precios
     */
    private function createOrder(string $code, OrderType $type, OrderStatus $status, Customer $customer, array $extra, array $lines, ?float $shipping = null): Order
    {
        $order = Order::firstOrCreate(['code' => $code], $extra + [
            'type' => $type,
            'status' => $status,
            'customer_id' => $customer->id,
            // Copia de los datos del cliente
            'customer_type' => $customer->type,
            'company_name' => $customer->company_name,
            'nit' => $customer->nit,
            'contact_name' => $customer->contact_name,
            'email' => $customer->user->email,
            'phone' => $customer->phone,
            'city' => $customer->city,
            'address' => $customer->address,
        ]);

        // Ya existía: no se repiten ítems ni totales
        if (! $order->wasRecentlyCreated) {
            return $order;
        }

        $total = 0;
        $base = 0;
        $priced = true;

        foreach ($lines as $line) {
            $product = Product::where('sku', $line[0])->firstOrFail();
            $quantity = $line[1];
            $unitPrice = $line[2] ?? $product->price;
            $lineTotal = $unitPrice === null ? null : round($unitPrice * $quantity, 2);

            $order->items()->create([
                'product_id' => $product->id,
                // Copia de los datos del producto
                'sku' => $product->sku,
                'product_name' => $product->name,
                'unit' => $product->unit,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax_rate' => $product->tax_rate,
                'line_total' => $lineTotal,
            ]);

            if ($lineTotal === null) {
                $priced = false;
            } else {
                // El precio incluye IVA: base = total / (1 + tasa)
                $total += $lineTotal;
                $base += $lineTotal / (1 + $product->tax_rate / 100);
            }
        }

        if ($priced && $shipping !== null) {
            $subtotal = round($base, 2);

            // IVA = total - base redondeada, para que base + IVA cuadre al centavo
            $order->update([
                'subtotal' => $subtotal,
                'tax' => round($total - $subtotal, 2),
                'shipping' => $shipping,
                'total' => $total + $shipping,
            ]);
        }

        return $order;
    }

    /**
     * @param  array<int, array{0: ?OrderStatus, 1: OrderStatus, 2: ?User, 3: ?string, 4: Carbon}>  $changes
     */
    private function history(Order $order, array $changes): void
    {
        // Ya existía: su historial ya está guardado
        if (! $order->wasRecentlyCreated) {
            return;
        }

        foreach ($changes as [$from, $to, $user, $note, $at]) {
            $order->statusHistories()->make([
                'user_id' => $user?->id,
                'from_status' => $from,
                'to_status' => $to,
                'note' => $note,
            ])->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
        }

        // La solicitud nace en la fecha de su primer estado
        $order->forceFill(['created_at' => $changes[0][4]])->save();
    }
}
