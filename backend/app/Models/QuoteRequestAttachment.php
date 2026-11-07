<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Archivo adjunto de una cotización libre (foto, placa del equipo o lista de materiales), guardado en base64. */
#[Fillable(['quote_request_id', 'content_base64'])]
class QuoteRequestAttachment extends Model
{
    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function contents(): string
    {
        return base64_decode($this->content_base64, true) ?: '';
    }
}
