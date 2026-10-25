<?php

namespace App\Enums;

enum ProductUnit: string
{
    case Unidad = 'unidad';
    case Metro = 'metro';
    case Rollo = 'rollo';
    case Caja = 'caja';

    public function label(): string
    {
        return match ($this) {
            self::Unidad => 'Unidad',
            self::Metro => 'Metro',
            self::Rollo => 'Rollo',
            self::Caja => 'Caja',
        };
    }
}
