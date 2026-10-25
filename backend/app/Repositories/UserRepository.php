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

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    /** role e is_active no son asignables en masa, así que se ponen aquí de forma explícita. */
    public function createClient(array $data): User
    {
        $user = new User($data);
        $user->role = UserRole::Cliente;
        $user->is_active = true;
        $user->save();

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user;
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
