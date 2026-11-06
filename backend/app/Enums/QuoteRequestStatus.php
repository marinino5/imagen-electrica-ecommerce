<?php

namespace App\Enums;

/** Estado de una solicitud de cotización enviada sin cuenta. */
enum QuoteRequestStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Nueva',
            self::Contacted => 'Contactada',
            self::Closed => 'Cerrada',
        };
    }
}
