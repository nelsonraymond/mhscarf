<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class ReviewReminderMail extends Mailable
{
    public function __construct(public Order $order, public Collection $items) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Bagaimana hijab Anda? Beri ulasan untuk pesanan #{$this->order->order_number}");
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.review-reminder');
    }
}