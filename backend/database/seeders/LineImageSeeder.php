<?php

namespace Database\Seeders;

use App\Models\ProductLine;
use Illuminate\Database\Seeder;

/**
 * Asigna la foto de cada línea desde database/data/line-images.json ({"slug-de-la-linea": "/img/productos/familia/marca-linea.webp"}).
 *
 * Solo llena imágenes VACÍAS: una foto que el equipo ya cambió no se pisa. Es idempotente, así que se puede volver a
 * correr (php artisan db:seed --class=LineImageSeeder) cada vez que se agreguen entradas al manifiesto.
 * El origen de cada archivo se registra en docs/creditos-media.md; lo que falta, en docs/imagenes-pendientes.md.
 */
class LineImageSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/line-images.json');
        $map = is_file($path) ? json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR) : [];

        foreach ($map as $slug => $url) {
            ProductLine::where('slug', $slug)
                ->where(fn ($q) => $q->whereNull('image_url')->orWhere('image_url', ''))
                ->update(['image_url' => $url]);
        }
    }
}
