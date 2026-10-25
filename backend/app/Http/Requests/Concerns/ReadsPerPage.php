<?php

namespace App\Http\Requests\Concerns;

/** Tamaño de página que pide el cliente (?per_page=), acotado entre 1 y el máximo. */
trait ReadsPerPage
{
    protected function perPage(int $default, int $max): int
    {
        return min(max($this->integer('per_page', $default), 1), $max);
    }
}
