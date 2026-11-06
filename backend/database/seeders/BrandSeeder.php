<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        // Cestari se distribuye como WEG Cestari: si ya existía la marca antigua, se renombra sin perder sus productos
        $legacy = Brand::where('slug', 'cestari')->first();
        if ($legacy && ! Brand::where('slug', 'weg-cestari')->exists()) {
            $legacy->update(['name' => 'WEG Cestari', 'slug' => 'weg-cestari']);
        }

        $brands = ['WEG', 'WEG Cestari', 'WEG Solar', 'Siemens', 'Innomotics', 'Nexans', 'Sylvania', 'Soler & Palau',
            // Marcas de las referencias del inventario y de las líneas bajo pedido
            'Nord', 'Nidec', 'ABB', 'Match Drive', 'EMI', 'AIRAL', 'YORK', 'FLUKE', 'Centelsa by Nexans'];

        foreach ($brands as $name) {
            // logo_url queda nulo hasta tener la URL pública de cada logo
            Brand::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
    }
}
