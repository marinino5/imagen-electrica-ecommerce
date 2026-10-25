<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\User;

class CustomerRepository
{
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
