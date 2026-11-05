<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * One visitor message, delivered to one fixed address.
 *
 * The recipient is never taken from the request, so the form cannot be pointed at
 * somebody else's inbox; and the body is sent as plain text, so a message carries
 * no tracking pixel, no remote image and no HTML a mail client would render.
 */
class ContactMessage extends Mailable
{
    public function __construct(
        public readonly string $visitorName,
        public readonly string $visitorEmail,
        public readonly string $heading,
        public readonly string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->heading,
            replyTo: [new Address($this->visitorEmail, $this->visitorName)],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.contact');
    }
}
