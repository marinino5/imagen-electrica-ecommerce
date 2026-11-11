<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderIndexRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\View\View;

/** Listado y detalle de pedidos y cotizaciones (admin y comercial). */
class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function index(OrderIndexRequest $request): View|\Illuminate\Http\RedirectResponse
    {
        // «Pedidos» es una pestaña de Ventas; las cotizaciones (con y sin cuenta) están juntas en su propia pestaña
        if ($request->query('type') === OrderType::Quote->value) {
            return redirect()->route('admin.quotes.index', array_filter(['q' => $request->query('q')]));
        }

        $filters = ['type' => OrderType::Purchase->value] + $request->filters();
        $filters['type'] = OrderType::Purchase->value;

        return view('admin.orders.index', [
            'filters' => $filters,
            'orders' => $this->orders->paginate($filters),
            'types' => OrderType::cases(),
            'statuses' => [OrderStatus::PendingPayment, OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Delivered, OrderStatus::Cancelled],
            'overdueHours' => OrderService::QUOTE_RESPONSE_HOURS,
        ]);
    }

    public function show(Order $order): View
    {
        $order = $this->orders->details($order);

        return view('admin.orders.show', [
            'order' => $order,
            'transitions' => $this->orders->allowedTransitions($order),
            'canPrice' => $this->orders->canPrice($order),
            'missingPrices' => $this->orders->missingPrices($order),
            'commercials' => $this->orders->commercials(),
        ]);
    }
}
