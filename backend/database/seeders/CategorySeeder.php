<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Categorías y subcategorías del catálogo de Imagen Eléctrica.
 * Idempotente: busca por slug y no modifica las que ya existen.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Motorreductores' => ['Sinfín corona', 'Coaxiales'],
            'Motores eléctricos' => ['Motores trifásicos', 'Motores monofásicos'],
            'Variadores y arrancadores' => ['Variadores de frecuencia', 'Arrancadores suaves', 'Guardamotores'],
            'Ventilación' => ['Extractores', 'Ventiladores axiales', 'Ventiladores de techo'],
            'Iluminación' => ['Reflectores LED', 'Tubos LED'],
            'Cables' => ['Cables THHN', 'Cables encauchetados'],
            'Compresores' => ['Compresores de pistón', 'Compresores verticales'],
            'PLC' => ['PLC compactos', 'Relés programables'],
            'Energía solar' => ['Paneles solares', 'Inversores solares'],
        ];

        $order = 0;

        foreach ($tree as $parentName => $children) {
            $parent = Category::firstOrCreate(
                ['slug' => Str::slug($parentName)],
                ['name' => $parentName, 'sort_order' => ++$order],
            );

            foreach ($children as $i => $childName) {
                $parent->children()->firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    ['name' => $childName, 'sort_order' => $i + 1],
                );
            }
        }
    }
}
