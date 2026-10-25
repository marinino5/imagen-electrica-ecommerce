<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Comercial = 'comercial';
    case Cliente = 'cliente';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Comercial => 'Comercial',
            self::Cliente => 'Cliente',
        };
    }
}
