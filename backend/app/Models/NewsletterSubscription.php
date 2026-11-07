<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Persona suscrita al boletín: correo, autorización de datos, fecha e IP desde donde se autorizó. */
#[Fillable(['email', 'consent', 'consented_at', 'ip'])]
class NewsletterSubscription extends Model
{
    protected function casts(): array
    {
        return [
            'consent' => 'boolean',
            'consented_at' => 'datetime',
        ];
    }
}
