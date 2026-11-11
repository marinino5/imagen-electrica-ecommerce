<?php

namespace App\Enums;

/**
 * Tipo de documento del comprador. El valor es el código `id_type` de la API de clientes de Siigo (developers.siigo.com):
 * 13 cédula de ciudadanía, 31 NIT, 22 cédula de extranjería, 41 pasaporte.
 */
enum IdType: string
{
    case CC = '13';
    case NIT = '31';
    case CE = '22';
    case PP = '41';

    public function short(): string
    {
        return match ($this) {
            self::CC => 'CC',
            self::NIT => 'NIT',
            self::CE => 'CE',
            self::PP => 'Pasaporte',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::CC => 'Cédula de ciudadanía',
            self::NIT => 'NIT',
            self::CE => 'Cédula de extranjería',
            self::PP => 'Pasaporte',
        };
    }

    /** CC y NIT: solo dígitos (3 a 13). CE y pasaporte: letras y números (1 a 20). */
    public function isNumeric(): bool
    {
        return $this === self::CC || $this === self::NIT;
    }
}
