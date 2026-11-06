<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductLine;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

/**
 * Dos promociones de ejemplo SOLO para desarrollo local (la corre DatabaseSeeder cuando APP_ENV=local).
 * En producción las crea el administrador desde el panel; si no hay ninguna vigente, la tienda oculta la sección.
 */
class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $examples = [
            ['line' => 'contactores', 'title' => 'Contactores con 12 % menos', 'description' => 'Para tableros de arranque y control.', 'discount_percent' => 12, 'special_price' => null],
            ['line' => 'variador-cfw300', 'title' => 'Variadores CFW300 a precio especial', 'description' => 'Hasta agotar existencias en bodega.', 'discount_percent' => 8, 'special_price' => null],
        ];

        foreach ($examples as $e) {
            $line = ProductLine::where('slug', $e['line'])->first();
            if (! $line) {
                continue;
            }

            Promotion::firstOrCreate(['title' => $e['title']], [
                'description' => $e['description'],
                'product_line_id' => $line->id,
                'product_id' => null,
                'discount_percent' => $e['discount_percent'],
                'special_price' => $e['special_price'],
                'starts_at' => today()->subDay(),
                'ends_at' => today()->addDays(30),
                'is_active' => true,
            ]);
        }
    }
}
