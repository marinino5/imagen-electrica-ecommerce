<?php

namespace App\Repositories;

use App\Models\NewsletterSubscription;
use App\Repositories\Concerns\EscapesLike;
use Illuminate\Pagination\LengthAwarePaginator;

class NewsletterRepository
{
    use EscapesLike;

    /** @return LengthAwarePaginator<int, NewsletterSubscription> */
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return NewsletterSubscription::query()
            ->when($search, fn ($q, string $term) => $q->where('email', 'like', $this->likePattern($term)))
            ->latest('consented_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Crea la suscripción o, si el correo ya estaba, deja la original (no se duplica ni se reescribe la prueba). */
    public function firstOrCreate(string $email, array $attributes): NewsletterSubscription
    {
        return NewsletterSubscription::firstOrCreate(['email' => $email], $attributes);
    }
}
