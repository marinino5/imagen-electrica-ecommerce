<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\GuestOrderLink;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

/** Correo al comprador sin cuenta: resumen de lo que pidió y el enlace seguro para ver el estado. */
class GuestOrderReceived extends Mailable
{
    /** @param  Collection<int, Order>  $orders */
    public function __construct(public readonly Collection $orders) {}

    public function envelope(): Envelope
    {
        $codes = $this->orders->pluck('code')->implode(' y ');

        return new Envelope(subject: "Recibimos tu solicitud {$codes} · Imagen Eléctrica");
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.guest-order',
            text: 'mail.guest-order-text',
            with: ['links' => $this->orders->mapWithKeys(fn (Order $o) => [$o->code => GuestOrderLink::storefrontUrl($o->code)])->all()],
        );
    }
}
