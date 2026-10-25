<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    /** @return Collection<int, User> */
    public function activeCommercials(): Collection
    {
        return User::query()
            ->where('role', UserRole::Comercial)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function findActiveCommercial(int $id): ?User
    {
        return User::query()
            ->whereKey($id)
            ->where('role', UserRole::Comercial)
            ->where('is_active', true)
            ->first();
    }
}
