<?php

namespace App\Services\Concerns;

use Illuminate\Support\Str;

/**
 * Genera el slug a partir del nombre y le agrega -2, -3... si ya existe.
 * El Service que lo use debe indicar cómo consultar si el slug está ocupado.
 */
trait GeneratesUniqueSlug
{
    abstract protected function slugExists(string $slug): bool;

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;

        for ($i = 2; $this->slugExists($slug); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
