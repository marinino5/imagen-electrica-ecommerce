<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\QuoteInboxService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Pestaña «Cotizaciones» de Ventas: las de clientes con cuenta y las enviadas sin cuenta, juntas (admin y comercial). */
class QuoteInboxController extends Controller
{
    public function __construct(private readonly QuoteInboxService $quotes) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'origin' => ['nullable', 'in:account,free'],
            'state' => ['nullable', 'in:pending,answered,cancelled'],
        ]) + ['q' => null, 'origin' => null, 'state' => null];

        return view('admin.quotes.index', [
            'filters' => $filters,
            'quotes' => $this->quotes->paginate($filters),
            'overdueHours' => OrderService::QUOTE_RESPONSE_HOURS,
        ]);
    }
}
