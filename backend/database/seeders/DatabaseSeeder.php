<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // El orden importa: cada seeder usa datos de los anteriores.
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
            CatalogSeeder::class, // líneas y referencias del inventario (351 SKU)
            LineImageSeeder::class, // fotos oficiales de las líneas (database/data/line-images.json)
            LegacyCategoriesSeeder::class, // pasa las categorías de la primera versión a las 10 familias de la tienda
            OrderSeeder::class,
        ]);

        // Promociones de ejemplo: solo en desarrollo local, nunca en producción
        if (app()->environment('local')) {
            $this->call(PromotionSeeder::class);
        }
    }
}
