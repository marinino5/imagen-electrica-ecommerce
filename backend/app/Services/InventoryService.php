<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Order;
use App\Repositories\ProductRepository;

/**
 * Reglas de inventario. El stock solo se descuenta cuando se confirma el pago.
 */
class InventoryService
{
    public function __construct(private readonly ProductRepository $products) {}

    /**
     * Valida el stock de cada línea y lo descuenta. Debe llamarse dentro de
     * una transacción: si falta stock de cualquier línea no se descuenta nada.
     *
     * El stock es entero y las cantidades pueden ser decimales (50,5 metros de
     * cable): se descuenta la cantidad redondeada hacia arriba para no vender
     * más de lo que hay.
     *
     * @throws BusinessRuleException si algún producto no existe o no alcanza
     */
    public function deductForOrder(Order $order): void
    {
        $items = $order->items()->get();
        $locked = $this->products->lockMany($items->pluck('product_id')->filter()->all());

        // Si un producto aparece en dos líneas, se valida la suma
        $needed = [];

        foreach ($items as $item) {
            $product = $locked->get($item->product_id);

            if (! $product || $product->trashed()) {
                throw new BusinessRuleException("El producto «{$item->product_name}» ya no está disponible.");
            }

            $needed[$product->id] = ($needed[$product->id] ?? 0) + (int) ceil((float) $item->quantity);

            if ($product->stock < $needed[$product->id]) {
                throw new BusinessRuleException(
                    "Stock insuficiente de «{$item->product_name}»: se piden {$needed[$product->id]} y hay {$product->stock}."
                );
            }
        }

        foreach ($needed as $productId => $quantity) {
            $this->products->decrementStock($locked->get($productId), $quantity);
        }
    }
}
