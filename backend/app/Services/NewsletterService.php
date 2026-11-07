<?php

namespace App\Services;

use App\Models\NewsletterSubscription;
use App\Repositories\NewsletterRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class NewsletterService
{
    public const PER_PAGE = 20;

    public function __construct(private readonly NewsletterRepository $subscriptions) {}

    /**
     * Suscribe un correo guardando la autorización de datos con su fecha y su IP. Si el correo ya estaba suscrito
     * no se crea otro registro ni se cambia la fecha del consentimiento original.
     */
    public function subscribe(string $email, ?string $ip): NewsletterSubscription
    {
        return $this->subscriptions->firstOrCreate(mb_strtolower(trim($email)), [
            'consent' => true,
            'consented_at' => now(),
            'ip' => $ip,
        ]);
    }

    /** @return LengthAwarePaginator<int, NewsletterSubscription> */
    public function paginate(?string $search, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return $this->subscriptions->paginate($search, $perPage);
    }
}
