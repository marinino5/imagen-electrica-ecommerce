<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Aviso interno a Imagen Eléctrica: llegó una cotización libre. Lleva el adjunto, si lo hay. */
class FreeQuoteReceived extends Mailable
{
    public function __construct(public readonly QuoteRequest $quote) {}

    public function envelope(): Envelope
    {
        $replyTo = $this->quote->email ? [new \Illuminate\Mail\Mailables\Address($this->quote->email, $this->quote->name)] : [];

        return new Envelope(subject: 'Cotización libre '.$this->quote->reference().' · '.$this->quote->name, replyTo: $replyTo);
    }

    public function content(): Content
    {
        return new Content(text: 'mail.free-quote');
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        $file = $this->quote->attachment;

        if (! $file) {
            return [];
        }

        return [Attachment::fromData(fn () => $file->contents(), $this->quote->attachment_name)->withMime($this->quote->attachment_mime)];
    }
}
