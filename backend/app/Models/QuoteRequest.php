<?php

namespace App\Models;

use App\Enums\QuoteRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Solicitud de cotización sin cuenta: datos de contacto y copia de los renglones pedidos. */
#[Fillable(['name', 'phone', 'email', 'city', 'company', 'nit', 'message', 'items', 'privacy_accepted_at', 'ip'])]
class QuoteRequest extends Model
{
    protected function casts(): array
    {
        return [
            'items' => 'array',
            'status' => QuoteRequestStatus::class,
            'privacy_accepted_at' => 'datetime',
        ];
    }
}
