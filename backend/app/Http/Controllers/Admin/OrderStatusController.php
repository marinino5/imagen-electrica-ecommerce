<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;

/** Cambio de estado: OrderService valida la transición y guarda el historial. */
class OrderStatusController extends Controller
{
    public function __invoke(OrderStatusRequest $request, Order $order, OrderService $orders): RedirectResponse
    {
        $order = $orders->changeStatus($order, $request->newStatus(), $request->user(), $request->validated('note'));

        return redirect()->route('admin.orders.show', $order)
            ->with('status', "{$order->code} ahora está «{$order->status->label()}».");
    }
}
