<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscription;
use App\Services\NewsletterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/** Suscriptores del boletín (pie de la tienda): listado, exportar CSV y dar de baja (admin y comercial). Aquí no se envía nada. */
class NewsletterController extends Controller
{
    public function __construct(private readonly NewsletterService $newsletter) {}

    public function index(Request $request): View
    {
        [$search, $status] = $this->filters($request);

        return view('admin.newsletter.index', [
            'search' => $search,
            'status' => $status,
            'subscriptions' => $this->newsletter->paginate($search, $status),
            'counts' => $this->newsletter->counts(),
            'consentVersion' => config('shop.newsletter_consent_version'),
        ]);
    }

    public function export(Request $request): Response
    {
        [$search, $status] = $this->filters($request);

        return response($this->newsletter->csv($search, $status), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="suscriptores-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    public function unsubscribe(NewsletterSubscription $subscription): RedirectResponse
    {
        $this->newsletter->unsubscribe($subscription);

        return back()->with('status', "{$subscription->email} quedó dado de baja: no recibirá correos.");
    }

    /** @return array{0: ?string, 1: ?string} */
    private function filters(Request $request): array
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'estado' => ['nullable', 'in:active,unsubscribed']]);

        return [$data['q'] ?? null, $data['estado'] ?? null];
    }
}
