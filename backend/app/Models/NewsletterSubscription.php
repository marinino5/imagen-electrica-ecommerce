<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Persona suscrita al boletín: correo, autorización de datos (con la versión del texto y la fecha), IP y fecha de baja. */
#[Fillable(['email', 'consent', 'consent_version', 'consented_at', 'unsubscribed_at', 'ip'])]
class NewsletterSubscription extends Model
{
    protected function casts(): array
    {
        return [
            'consent' => 'boolean',
            'consented_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }
}
