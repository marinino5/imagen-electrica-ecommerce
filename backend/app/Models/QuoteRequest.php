<?php

namespace App\Models;

use App\Enums\QuoteRequestStatus;
use App\Enums\QuoteRequestType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Solicitud de cotización sin cuenta: del catálogo (renglones) o libre (texto y adjunto opcional). */
#[Fillable(['type', 'name', 'phone', 'email', 'city', 'address', 'company', 'nit', 'message', 'application', 'items', 'attachment_name', 'attachment_mime', 'attachment_size', 'privacy_accepted_at', 'ip'])]
class QuoteRequest extends Model
{
    protected function casts(): array
    {
        return [
            'items' => 'array',
            'type' => QuoteRequestType::class,
            'status' => QuoteRequestStatus::class,
            'privacy_accepted_at' => 'datetime',
        ];
    }

    public function attachment(): HasOne
    {
        return $this->hasOne(QuoteRequestAttachment::class);
    }

    public function isFree(): bool
    {
        return $this->type === QuoteRequestType::Free;
    }

    /** Número que ve la persona al enviar y que viaja en el mensaje de WhatsApp. */
    public function reference(): string
    {
        return sprintf('%s-%04d', $this->isFree() ? 'LIB' : 'SOL', $this->id);
    }
}
