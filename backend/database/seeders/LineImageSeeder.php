<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductLine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Asigna la foto de cada línea desde database/data/line-images.json. Cada entrada es la ruta de la imagen, o un objeto
 * {"image": ruta, "alt": ruta} cuando la línea tiene una segunda vista que se muestra al pasar el mouse:
 * {"slug-de-la-linea": "/img/catalogo/marca-linea.webp"}.
 *
 * Por defecto solo llena imágenes VACÍAS (foto y segunda vista por separado): una foto que el equipo ya cambió no se pisa.
 * Con `$replaceLegacy = true` (lo usa la migración del formato recortado) también reemplaza las rutas antiguas que puso
 * este mismo proyecto (/img/productos/… y /img/familias/…); una URL externa o una ruta propia nunca se pisa.
 *
 * Además copia la foto de la línea a cada una de sus referencias (products.image_url): el fabricante usa la misma foto
 * para todas las variantes de una línea, así que las referencias la comparten. Una referencia con foto propia no se toca.
 *
 * Es idempotente: se puede volver a correr (php artisan db:seed --class=LineImageSeeder) cada vez que se agreguen entradas.
 * El origen de cada archivo se registra en docs/creditos-media.md; lo que falta, en docs/imagenes-pendientes.md.
 */
class LineImageSeeder extends Seeder
{
    /** Prefijos de las rutas antiguas del proyecto que el formato recortado puede reemplazar. */
    private const LEGACY_PREFIXES = ['/img/productos/', '/img/familias/'];

    public function __construct(private readonly bool $replaceLegacy = false) {}

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

            $line = ProductLine::where('slug', $slug)->first();
            if (! $line) {
                continue;
            }

            if ($this->replaceable($line->image_url)) {
                $line->image_url = $image;
            }

            // La segunda vista se llena aparte y solo si está vacía: una línea puede haber recibido su foto antes de que
            // existiera la columna (migración anterior), y entonces la condición de arriba ya no la alcanza.
            if ($hasAlt && $alt && $this->replaceable($line->image_alt_url)) {
                $line->image_alt_url = $alt;
            }

            if ($line->isDirty()) {
                $line->save();
            }

            // Las referencias comparten la foto de su línea (solo las que no tienen una o conservan la ruta antigua)
            Product::withTrashed()->where('line_id', $line->id)
                ->where(fn ($q) => $q->whereNull('image_url')->orWhere('image_url', '')->when(
                    $this->replaceLegacy,
                    fn ($q) => collect(self::LEGACY_PREFIXES)->each(fn ($p) => $q->orWhere('image_url', 'like', $p.'%')),
                ))
                ->update(['image_url' => $line->image_url]);
        }
    }

    /** Vacía, o (solo en modo migración) una ruta antigua del propio proyecto. */
    private function replaceable(?string $current): bool
    {
        if ($current === null || $current === '') {
            return true;
        }

        if (! $this->replaceLegacy) {
            return false;
        }

        foreach (self::LEGACY_PREFIXES as $prefix) {
            if (str_starts_with($current, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
