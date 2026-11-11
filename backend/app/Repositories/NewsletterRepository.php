<?php

namespace App\Repositories;

use App\Models\NewsletterSubscription;
use App\Repositories\Concerns\EscapesLike;
use Illuminate\Pagination\LengthAwarePaginator;

class NewsletterRepository
{
    use EscapesLike;

    /** @return LengthAwarePaginator<int, NewsletterSubscription> */
    public function paginate(?string $search, int $perPage, ?string $status = null): LengthAwarePaginator
    {
        return $this->query($search, $status)
            ->latest('consented_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Todos los que cumplen el filtro, para exportar. @return \Illuminate\Support\Collection<int, NewsletterSubscription> */
    public function all(?string $search, ?string $status = null)
    {
        return $this->query($search, $status)->latest('consented_at')->latest('id')->get();
    }

    private function query(?string $search, ?string $status)
    {
        return NewsletterSubscription::query()
            ->when($status === 'active', fn ($q) => $q->whereNull('unsubscribed_at'))
            ->when($status === 'unsubscribed', fn ($q) => $q->whereNotNull('unsubscribed_at'))
            ->when($search, fn ($q, string $term) => $q->where('email', 'like', $this->likePattern($term)));
    }

    public function update(NewsletterSubscription $subscription, array $data): NewsletterSubscription
    {
        $subscription->update($data);

        return $subscription;
    }

    public function counts(): array
    {
        return [
            'total' => NewsletterSubscription::count(),
            'active' => NewsletterSubscription::whereNull('unsubscribed_at')->count(),
            'unsubscribed' => NewsletterSubscription::whereNotNull('unsubscribed_at')->count(),
        ];
    }

    /** Crea la suscripción o, si el correo ya estaba, deja la original (no se duplica ni se reescribe la prueba). */
    public function firstOrCreate(string $email, array $attributes): NewsletterSubscription
    {
        return NewsletterSubscription::firstOrCreate(['email' => $email], $attributes);
    }
}
