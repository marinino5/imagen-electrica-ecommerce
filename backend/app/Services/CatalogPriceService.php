<?php

namespace App\Services;

use App\Enums\SaleMode;
use App\Models\Product;
use App\Models\ProductLine;
use Illuminate\Support\Facades\DB;

/**
 * Carga de precios del catálogo por línea de código (Fase 1B). Las referencias nuevas llegan sin precio y se venden por
 * cotización; cuando la empresa entrega los precios, cada SKU con precio pasa a compra en línea:
 * price (con IVA) + sale_mode = direct + price_is_simulated = false. La línea pasa a directa si alguna de sus referencias lo es.
 * Lo que no trae precio no se toca. La disponibilidad sigue saliendo del stock (el modo no cambia al llegar a cero).
 */
class CatalogPriceService
{
    /**
     * @param  list<array{sku: string, price: string|int|float|null}>  $rows
     * @return array{updated: int, skipped: int, unknown: list<string>, invalid: list<string>}
     */
    public function load(array $rows, bool $dryRun = false): array
    {
        $result = ['updated' => 0, 'skipped' => 0, 'unknown' => [], 'invalid' => []];
        $lines = [];

        DB::transaction(function () use ($rows, $dryRun, &$result, &$lines) {
            foreach ($rows as $row) {
                $sku = trim((string) ($row['sku'] ?? ''));
                $raw = $row['price'] ?? null;
                if ($sku === '' || $raw === null || trim((string) $raw) === '') {
                    $result['skipped']++;

                    continue;
                }

                $price = $this->parsePrice((string) $raw);
                if ($price === null || $price <= 0) {
                    $result['invalid'][] = $sku;

                    continue;
                }

                $product = Product::query()->where('sku', $sku)->first();
                if (! $product) {
                    $result['unknown'][] = $sku;

                    continue;
                }

                if (! $dryRun) {
                    $product->update(['price' => $price, 'sale_mode' => SaleMode::Direct, 'price_is_simulated' => false]);
                    if ($product->line_id) {
                        $lines[$product->line_id] = true;
                    }
                }
                $result['updated']++;
            }

            foreach (array_keys($lines) as $lineId) {
                ProductLine::whereKey($lineId)->update(['sale_mode' => SaleMode::Direct]);
            }
        });

        return $result;
    }

    /** «1.250.000», «1250000» o «1250000,50»: el punto es de miles y la coma, de decimales (formato colombiano). */
    private function parsePrice(string $raw): ?float
    {
        $clean = preg_replace('/[^\d.,]/', '', $raw) ?? '';
        if ($clean === '') {
            return null;
        }
        if (str_contains($clean, ',')) {
            $clean = str_replace(',', '.', str_replace('.', '', $clean));
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $clean)) {
            $clean = str_replace('.', '', $clean);
        }

        return is_numeric($clean) ? round((float) $clean, 2) : null;
    }
}
