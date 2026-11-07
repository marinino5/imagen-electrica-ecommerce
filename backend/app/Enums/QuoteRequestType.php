<?php

namespace App\Enums;

/** Origen de la solicitud: armada con productos del catálogo o escrita libremente (sin elegir productos). */
enum QuoteRequestType: string
{
    case Catalog = 'catalog';
    case Free = 'free';

    public function label(): string
    {
        return match ($this) {
            self::Catalog => 'Con productos',
            self::Free => 'Cotización libre',
        };
    }
}
