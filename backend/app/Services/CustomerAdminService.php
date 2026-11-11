<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Repositories\CustomerRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Clientes en el panel: listado y ficha con todo su historial (pedidos, cotizaciones y cotizaciones sin cuenta). */
class CustomerAdminService
{
    public const PER_PAGE = 15;

    public function __construct(private readonly CustomerRepository $customers) {}

    /** @return LengthAwarePaginator<int, Customer> */
    public function paginate(?string $search): LengthAwarePaginator
    {
        return $this->customers->paginate($search ? trim($search) : null, self::PER_PAGE);
    }

    /** @return array{customer: Customer, purchases: Collection, quotes: Collection, free: Collection, spent: float} */
    public function history(Customer $customer): array
    {
        $customer = $this->customers->withHistory($customer);
        $email = $customer->user?->email;

        return [
            'customer' => $customer,
            'purchases' => $customer->orders->where('type.value', 'purchase')->values(),
            'quotes' => $customer->orders->where('type.value', 'quote')->values(),
            'free' => $email ? QuoteRequest::where('email', $email)->latest()->get() : collect(),
            'spent' => (float) $customer->orders->where('type.value', 'purchase')->whereIn('status.value', ['paid', 'shipped', 'delivered'])->sum('total'),
        ];
    }
}
