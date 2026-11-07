<?php

namespace App\Services;

use App\Enums\QuoteRequestType;
use App\Mail\FreeQuoteReceived;
use App\Models\Product;
use App\Models\ProductLine;
use App\Models\QuoteRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class QuoteRequestService
{
    public const PER_PAGE = 15;

    /**
     * Guarda la solicitud con una copia de cada renglón (sku, nombre, variante): si el producto cambia
     * después, la solicitud no cambia. No toca inventario ni crea pedidos: un asesor responde.
     *
     * @param  array<string, mixed>  $data  datos validados de StoreQuoteRequestRequest
     */
    public function create(array $data, ?string $ip): QuoteRequest
    {
        return QuoteRequest::create([
            'type' => QuoteRequestType::Catalog,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'city' => $data['city'],
            'company' => $data['company'] ?? null,
            'nit' => $data['nit'] ?? null,
            'message' => $data['message'] ?? null,
            'items' => $this->snapshotItems($data['items']),
            'privacy_accepted_at' => now(),
            'ip' => $ip,
        ]);
    }

    /**
     * Cotización libre: texto y adjunto opcional, sin renglones de catálogo. El adjunto se guarda en la base de datos
     * y se avisa por correo a la empresa; si el correo falla, la solicitud ya quedó guardada y solo se registra el error.
     *
     * @param  array<string, mixed>  $data  datos validados de StoreFreeQuoteRequestRequest
     */
    public function createFree(array $data, ?UploadedFile $attachment, ?string $ip): QuoteRequest
    {
        $quote = DB::transaction(function () use ($data, $attachment, $ip) {
            $quote = QuoteRequest::create([
                'type' => QuoteRequestType::Free,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'city' => $data['city'],
                'address' => $data['address'],
                'company' => $data['company'] ?? null,
                'nit' => $data['nit'] ?? null,
                'message' => $data['message'],
                'application' => $data['application'] ?? null,
                'items' => [],
                'attachment_name' => $attachment?->getClientOriginalName(),
                'attachment_mime' => $attachment?->getMimeType(),
                'attachment_size' => $attachment?->getSize(),
                'privacy_accepted_at' => now(),
                'ip' => $ip,
            ]);

            $attachment && $quote->attachment()->create(['content_base64' => base64_encode((string) file_get_contents($attachment->getRealPath()))]);

            return $quote;
        });

        try {
            Mail::to(config('mail.quote_to'))->send(new FreeQuoteReceived($quote->load('attachment')));
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el aviso de cotización libre '.$quote->reference().': '.$e->getMessage());
        }

        return $quote;
    }

    /** @return LengthAwarePaginator<int, QuoteRequest> */
    public function paginate(?string $status, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return QuoteRequest::query()
            ->when($status, fn (Builder $q, string $s) => $q->where('status', $s))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @param  array<int, array<string, mixed>>  $items */
    private function snapshotItems(array $items): array
    {
        return array_map(function (array $item) {
            $quantity = (float) $item['quantity'];

            if (! empty($item['product_id'])) {
                $product = Product::with('line:id,name')->findOrFail($item['product_id']);

                return [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'label' => $product->variant_label,
                    'line' => $product->line?->name,
                    'quantity' => $quantity,
                ];
            }

            $line = ProductLine::where('slug', $item['line'])->firstOrFail();

            return ['product_id' => null, 'sku' => null, 'name' => $line->name, 'label' => null, 'line' => $line->name, 'quantity' => $quantity];
        }, $items);
    }
}
