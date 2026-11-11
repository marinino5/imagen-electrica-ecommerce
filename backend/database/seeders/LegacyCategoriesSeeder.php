<?php

namespace Database\Seeders;

use App\Services\CatalogLayoutService;
use Illuminate\Database\Seeder;

/** Las categorías de demostración de la primera versión pasan a las de la tienda (mismos nombres que ve el cliente). Idempotente. */
class LegacyCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        app(CatalogLayoutService::class)->migrateLegacyCategories();
    }
}
