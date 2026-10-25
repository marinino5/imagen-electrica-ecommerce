<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    case Pickup = 'pickup';
    case Local = 'local';
    case National = 'national';

    public function label(): string
    {
        return match ($this) {
            self::Pickup => 'Recoger en tienda',
            self::Local => 'Envío local (Bucaramanga y área metropolitana)',
            self::National => 'Envío nacional',
        };
    }
}
