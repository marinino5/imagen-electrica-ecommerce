<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductLine;
use App\Models\QuoteRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

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
