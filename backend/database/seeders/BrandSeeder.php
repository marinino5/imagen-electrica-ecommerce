<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = ['WEG', 'Siemens', 'Nexans', 'Sylvania', 'Delta', 'Soler & Palau', 'Cestari'];

        foreach ($brands as $name) {
            // logo_url queda nulo hasta tener la URL pública de cada logo
            Brand::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
    }
}
