<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderAssignmentRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;

class OrderAssignmentController extends Controller
{
    public function __invoke(OrderAssignmentRequest $request, Order $order, OrderService $orders): RedirectResponse
    {
        $orders->assign($order, $request->validated('assigned_to'));

        return redirect()->route('admin.orders.show', $order)->with('status', 'Responsable actualizado.');
    }
}
