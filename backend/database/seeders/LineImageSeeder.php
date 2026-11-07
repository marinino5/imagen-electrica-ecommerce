<?php

namespace Database\Seeders;

use App\Models\ProductLine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Asigna la foto de cada línea desde database/data/line-images.json. Cada entrada es la ruta de la imagen, o un objeto
 * {"image": ruta, "alt": ruta} cuando la línea tiene una segunda vista que se muestra al pasar el mouse:
 * {"slug-de-la-linea": "/img/productos/familia/marca-linea.webp"}.
 *
 * Solo llena imágenes VACÍAS (foto y segunda vista por separado): una foto que el equipo ya cambió no se pisa. Es idempotente, así que se puede volver a
 * correr (php artisan db:seed --class=LineImageSeeder) cada vez que se agreguen entradas al manifiesto.
 * El origen de cada archivo se registra en docs/creditos-media.md; lo que falta, en docs/imagenes-pendientes.md.
 */
class LineImageSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/line-images.json');
        $map = is_file($path) ? json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR) : [];

        // La migración que crea image_alt_url es posterior a otra que ya llama a este seeder: en una instalación nueva
        // la columna todavía no existe, y ahí tampoco hay líneas que actualizar.
        $hasAlt = Schema::hasColumn('product_lines', 'image_alt_url');

        foreach ($map as $slug => $entry) {
            $image = is_array($entry) ? $entry['image'] : $entry;
            $alt = is_array($entry) ? ($entry['alt'] ?? null) : null;

            ProductLine::where('slug', $slug)
                ->where(fn ($q) => $q->whereNull('image_url')->orWhere('image_url', ''))
                ->update(['image_url' => $image]);

            // La segunda vista se llena aparte y solo si está vacía: una línea puede haber recibido su foto antes de que
            // existiera la columna (migración anterior), y entonces la condición de arriba ya no la alcanza.
            if ($hasAlt && $alt) {
                ProductLine::where('slug', $slug)
                    ->where(fn ($q) => $q->whereNull('image_alt_url')->orWhere('image_alt_url', ''))
                    ->update(['image_alt_url' => $alt]);
            }
        }
    }
}
