<?php

namespace App\Console\Commands;

use App\Services\CatalogPriceService;
use Illuminate\Console\Command;

/**
 * php artisan catalog:load-prices docs/inventario/precios-pendientes.csv [--dry-run]
 *
 * El CSV lleva una fila por referencia con las columnas `sku` y `precio_con_iva` (las demás se ignoran). Las referencias con
 * precio pasan a compra en línea; las que lo traen vacío siguen en cotización.
 */
class LoadCatalogPrices extends Command
{
    protected $signature = 'catalog:load-prices {file : CSV con las columnas sku y precio_con_iva} {--dry-run : Muestra qué cambiaría sin guardar}';

    protected $description = 'Carga precios (con IVA) desde un CSV y pasa esas referencias a compra en línea';

    public function handle(CatalogPriceService $prices): int
    {
        $path = (string) $this->argument('file');
        if (! is_file($path) || ($handle = fopen($path, 'r')) === false) {
            $this->error("No se pudo leer el archivo: {$path}");

            return self::FAILURE;
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h, " \t\n\r\0\x0B\xEF\xBB\xBF")), (array) fgetcsv($handle));
        $skuAt = array_search('sku', $header, true);
        $priceAt = array_search('precio_con_iva', $header, true);
        if ($skuAt === false || $priceAt === false) {
            fclose($handle);
            $this->error('El CSV debe traer las columnas «sku» y «precio_con_iva».');

            return self::FAILURE;
        }

        $rows = [];
        while (($line = fgetcsv($handle)) !== false) {
            $rows[] = ['sku' => $line[$skuAt] ?? '', 'price' => $line[$priceAt] ?? null];
        }
        fclose($handle);

        $result = $prices->load($rows, (bool) $this->option('dry-run'));

        $this->info(($this->option('dry-run') ? '[simulación] ' : '')."Con precio: {$result['updated']} · sin precio (siguen en cotización): {$result['skipped']}");
        if ($result['unknown'] !== []) {
            $this->warn('SKU que no existen en el catálogo: '.implode(', ', $result['unknown']));
        }
        if ($result['invalid'] !== []) {
            $this->warn('Precio no válido: '.implode(', ', $result['invalid']));
        }

        return self::SUCCESS;
    }
}
