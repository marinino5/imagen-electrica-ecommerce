<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerAdminService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Clientes con cuenta: listado y ficha con su historial de pedidos y cotizaciones (admin y comercial). */
class CustomerController extends Controller
{
    public function __construct(private readonly CustomerAdminService $customers) {}

    public function index(Request $request): View
    {
        $search = $request->validate(['q' => ['nullable', 'string', 'max:100']])['q'] ?? null;

        return view('admin.customers.index', ['search' => $search, 'customers' => $this->customers->paginate($search)]);
    }

    public function show(Customer $customer): View
    {
        return view('admin.customers.show', $this->customers->history($customer));
    }
}
