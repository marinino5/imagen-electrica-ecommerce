<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderPricingRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;

class OrderPricingController extends Controller
{
    public function __invoke(OrderPricingRequest $request, Order $order, OrderService $orders): RedirectResponse
    {
        $orders->priceQuote($order, $request->validated('prices'), $request->validated('shipping'));

        return redirect()->route('admin.orders.show', $order)->with('status', 'Precios guardados.');
    }
}
