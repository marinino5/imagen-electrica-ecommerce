<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\User;
use App\Repositories\Concerns\EscapesLike;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository
{
    use EscapesLike;

    /**
     * Listado del panel con la cantidad de pedidos y de cotizaciones de cada cliente.
     *
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return Customer::query()
            ->with('user:id,email,is_active')
            ->withCount([
                'orders as purchases_count' => fn ($q) => $q->where('type', 'purchase'),
                'orders as quotes_count' => fn ($q) => $q->where('type', 'quote'),
            ])
            ->withSum(['orders as spent' => fn ($q) => $q->where('type', 'purchase')->whereIn('status', ['paid', 'shipped', 'delivered'])], 'total')
            ->when($search, function ($query, string $term) {
                $like = $this->likePattern($term);
                $query->where(fn ($q) => $q->where('company_name', 'like', $like)->orWhere('contact_name', 'like', $like)->orWhere('nit', 'like', $like)
                    ->orWhere('phone', 'like', $like)->orWhere('city', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', $like)));
            })
            ->latest()
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Cliente con su historial: pedidos y cotizaciones con cuenta, y las cotizaciones sin cuenta enviadas con el mismo correo. */
    public function withHistory(Customer $customer): Customer
    {
        return $customer->load(['user:id,email,is_active', 'orders' => fn ($q) => $q->latest()->latest('id')]);
    }

    /** ¿Ese NIT o cédula ya está en la ficha de otro cliente? (customers.nit es único) */
    public function nitTakenByAnother(string $nit, ?int $exceptCustomerId = null): bool
    {
        return Customer::query()->where('nit', $nit)->when($exceptCustomerId, fn ($q, int $id) => $q->where('id', '!=', $id))->exists();
    }

    public function createFor(User $user, array $data): Customer
    {
        return $user->customer()->create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer;
    }
}
