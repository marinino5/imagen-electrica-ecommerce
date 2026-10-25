<?php

namespace App\Repositories\Concerns;

trait EscapesLike
{
    /**
     * Patrón para LIKE '%texto%'. Los comodines "%" y "_" que escribió el
     * usuario se escapan para que se busquen como texto normal.
     */
    protected function likePattern(string $term): string
    {
        return '%'.addcslashes($term, '\\%_').'%';
    }
}
