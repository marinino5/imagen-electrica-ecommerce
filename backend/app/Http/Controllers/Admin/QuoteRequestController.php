<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QuoteRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Services\QuoteRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/** Listado y detalle de las cotizaciones que llegan sin cuenta desde la tienda (admin y comercial). */
class QuoteRequestController extends Controller
{
    public function __construct(private readonly QuoteRequestService $quotes) {}

    public function index(Request $request): View
    {
        $status = $request->validate(['status' => ['nullable', Rule::enum(QuoteRequestStatus::class)]])['status'] ?? null;

        return view('admin.quote-requests.index', [
            'status' => $status,
            'requests' => $this->quotes->paginate($status),
        ]);
    }

    public function show(QuoteRequest $quoteRequest): View
    {
        return view('admin.quote-requests.show', ['quote' => $quoteRequest->load('attachment')]);
    }

    /** Descarga el adjunto de una cotización libre (guardado en base de datos, no en disco). */
    public function attachment(QuoteRequest $quoteRequest): Response
    {
        $file = $quoteRequest->attachment;
        abort_if(! $file, 404);

        return response($file->contents(), 200, [
            'Content-Type' => $quoteRequest->attachment_mime ?: 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.addslashes($quoteRequest->attachment_name).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function update(Request $request, QuoteRequest $quoteRequest): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::enum(QuoteRequestStatus::class)]]);
        $quoteRequest->forceFill(['status' => $data['status']])->save();

        return back()->with('status', 'Estado actualizado.');
    }
}
