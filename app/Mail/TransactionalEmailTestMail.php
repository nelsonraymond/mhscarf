<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TransactionalEmailTestMail extends Mailable
{
    use Queueable;

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'MH SCARF — Transactional Email Test');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.transactional.test');
    }
}
