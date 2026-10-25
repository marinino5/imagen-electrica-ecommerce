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

    /**
     * Roles del personal de Imagen Eléctrica: los únicos que entran al panel.
     */
    public function canAccessPanel(): bool
    {
        return match ($this) {
            self::Admin, self::Comercial => true,
            self::Cliente => false,
        };
    }
}
