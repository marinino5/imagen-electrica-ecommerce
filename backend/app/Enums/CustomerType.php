<?php

namespace App\Enums;

enum CustomerType: string
{
    case Natural = 'natural';
    case Company = 'company';

    public function label(): string
    {
        return match ($this) {
            self::Natural => 'Persona natural',
            self::Company => 'Empresa',
        };
    }

    /** Las empresas deben dar razón social y NIT; la persona natural, solo su nombre. */
    public function requiresCompanyData(): bool
    {
        return $this === self::Company;
    }
}
