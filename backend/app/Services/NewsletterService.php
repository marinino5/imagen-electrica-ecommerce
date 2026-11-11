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
        $email = mb_strtolower(trim($email));
        $subscription = $this->subscriptions->firstOrCreate($email, [
            'consent' => true,
            'consent_version' => config('shop.newsletter_consent_version'),
            'consented_at' => now(),
            'ip' => $ip,
        ]);

        // Quien se dio de baja y vuelve a suscribirse acepta de nuevo: se registra el consentimiento nuevo (versión, fecha e IP)
        if ($subscription->unsubscribed_at !== null) {
            $subscription = $this->subscriptions->update($subscription, [
                'consent' => true,
                'consent_version' => config('shop.newsletter_consent_version'),
                'consented_at' => now(),
                'unsubscribed_at' => null,
                'ip' => $ip,
            ]);
        }

        return $subscription;
    }

    /** Da de baja (no se borra: queda la prueba del consentimiento y de que ya no quiere recibir correos). */
    public function unsubscribe(NewsletterSubscription $subscription): NewsletterSubscription
    {
        return $subscription->unsubscribed_at ? $subscription : $this->subscriptions->update($subscription, ['unsubscribed_at' => now()]);
    }

    /** @return LengthAwarePaginator<int, NewsletterSubscription> */
    public function paginate(?string $search, ?string $status = null, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return $this->subscriptions->paginate($search, $perPage, $status);
    }

    /** @return array{total: int, active: int, unsubscribed: int} */
    public function counts(): array
    {
        return $this->subscriptions->counts();
    }

    /** Líneas del CSV (con BOM para Excel): correo, estado, fecha y versión del consentimiento, IP. */
    public function csv(?string $search, ?string $status): string
    {
        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['correo', 'estado', 'consentimiento_aceptado', 'version_del_texto', 'fecha_consentimiento', 'fecha_baja', 'ip']);

        foreach ($this->subscriptions->all($search, $status) as $s) {
            fputcsv($out, [
                $s->email,
                $s->unsubscribed_at ? 'baja' : 'activo',
                $s->consent ? 'sí' : 'no',
                $s->consent_version,
                $s->consented_at->timezone(config('app.timezone'))->format('Y-m-d H:i'),
                $s->unsubscribed_at?->timezone(config('app.timezone'))->format('Y-m-d H:i'),
                $s->ip,
            ]);
        }
        rewind($out);

        return (string) stream_get_contents($out);
    }
}
