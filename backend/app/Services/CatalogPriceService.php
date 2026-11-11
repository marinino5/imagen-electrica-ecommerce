<?php

namespace App\Services;

use App\Enums\PriceSource;
use App\Enums\SaleMode;
use App\Models\Product;
use App\Models\ProductLine;
use Illuminate\Support\Facades\DB;

/**
 * Carga de precios del catálogo por línea de código (Fase 1B). Las referencias nuevas llegan sin precio y se venden por
 * cotización; cuando la empresa entrega los precios, cada SKU con precio pasa a compra en línea:
 * price (con IVA) + sale_mode = direct + price_is_simulated = false + price_source = null (el precio de referencia queda reemplazado por el real). La línea pasa a directa si alguna de sus referencias lo es.
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
                    $product->update(['price' => $price, 'sale_mode' => SaleMode::Direct, 'price_is_simulated' => false, 'price_source' => null]);
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

    /**
     * Precios de referencia de las referencias nuevas (database/data/precios-referencia.csv; fuentes en
     * docs/inventario/precios-referencia.csv). Solo toca productos SIN precio y sin origen de precio: nunca pisa un precio
     * real, simulado ni ya cargado, así que se puede llamar las veces que sea (migración y seeder).
     *
     * @return int productos actualizados
     */
    public function loadReferencePrices(?string $path = null): int
    {
        $path ??= database_path('data/precios-referencia.csv');
        if (! is_file($path) || ($handle = fopen($path, 'r')) === false) {
            return 0;
        }

        fgetcsv($handle); // encabezado: sku,price
        $updated = 0;
        $lines = [];

        while (($row = fgetcsv($handle)) !== false) {
            $sku = trim((string) ($row[0] ?? ''));
            $price = (float) ($row[1] ?? 0);
            if ($sku === '' || $price <= 0) {
                continue;
            }

            $product = Product::query()->where('sku', $sku)->whereNull('price')->whereNull('price_source')->first();
            if (! $product) {
                continue;
            }

            $product->update(['price' => $price, 'sale_mode' => SaleMode::Direct, 'price_is_simulated' => false, 'price_source' => PriceSource::Reference]);
            $updated++;
            if ($product->line_id) {
                $lines[$product->line_id] = true;
            }
        }
        fclose($handle);

        // La línea pasa a directa si alguna de sus referencias lo es (igual que load())
        foreach (array_keys($lines) as $lineId) {
            ProductLine::whereKey($lineId)->update(['sale_mode' => SaleMode::Direct]);
        }

        return $updated;
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
