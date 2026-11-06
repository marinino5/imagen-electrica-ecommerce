<?php

namespace Database\Seeders;

use App\Models\ProductLine;
use Illuminate\Database\Seeder;

/**
 * Catálogo por líneas para producción: marcas + CatalogSeeder (33 líneas y 351 referencias).
 *
 * Lo corre docker/entrypoint.sh en cada arranque, pero solo actúa cuando todavía no hay líneas:
 * así el primer despliegue con esta versión llena el catálogo sin tocar el panel de Render, y los
 * siguientes no vuelven a crear una línea que alguien eliminó a propósito. SEED_CATALOG=force
 * lo vuelve a correr (es idempotente: no pisa precios ni stock ya cambiados).
 */
class ProductionCatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (ProductLine::exists() && env('SEED_CATALOG') !== 'force') {
            $this->command?->info('Catálogo por líneas ya cargado: no se vuelve a sembrar.');

            return;
        }

        $this->call([BrandSeeder::class, CatalogSeeder::class]);
    }
}
