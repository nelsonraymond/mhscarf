<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;
use Symfony\Component\Mime\Email;

class ReviewReminderMail extends Mailable
{
    public function __construct(
        public Order $order,
        public Collection $items,
        public string $idempotencyKey,
        public ?array $payloadSnapshot = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->payloadSnapshot['subject'] ?? "Bagaimana hijab Anda? Beri ulasan untuk pesanan #{$this->order->order_number}",
            using: fn (Email $message) => $message->getHeaders()
                ->addTextHeader('X-Resend-Idempotency-Key', $this->idempotencyKey),
        );
    }

    public function content(): Content
    {
        return $this->payloadSnapshot
            ? new Content(htmlString: $this->payloadSnapshot['html'])
            : new Content(view: 'emails.review-reminders');
    }
}
