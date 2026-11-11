<?php

namespace App\Enums;

/** Tipo de documento del comprador para la factura electrónica (códigos de la DIAN, los mismos que usa Siigo). */
enum DocumentType: string
{
    case CC = 'CC';
    case CE = 'CE';
    case PP = 'PP';
    case NIT = 'NIT';

    public function label(): string
    {
        return match ($this) {
            self::CC => 'Cédula de ciudadanía',
            self::CE => 'Cédula de extranjería',
            self::PP => 'Pasaporte',
            self::NIT => 'NIT',
        };
    }

    /** Código del tipo de documento en la facturación electrónica de la DIAN (también el que espera Siigo). */
    public function dianCode(): string
    {
        return match ($this) {
            self::CC => '13',
            self::CE => '22',
            self::PP => '41',
            self::NIT => '31',
        };
    }

    /** El pasaporte lleva letras y números; los demás, solo dígitos. */
    public function isNumeric(): bool
    {
        return $this !== self::PP;
    }
}
