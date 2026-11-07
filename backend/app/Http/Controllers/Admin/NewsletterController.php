<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NewsletterService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Listado de las personas suscritas al boletín desde la tienda (admin y comercial). */
class NewsletterController extends Controller
{
    public function __construct(private readonly NewsletterService $newsletter) {}

    public function index(Request $request): View
    {
        $search = $request->validate(['q' => ['nullable', 'string', 'max:100']])['q'] ?? null;

        return view('admin.newsletter.index', [
            'search' => $search,
            'subscriptions' => $this->newsletter->paginate($search),
        ]);
    }
}
