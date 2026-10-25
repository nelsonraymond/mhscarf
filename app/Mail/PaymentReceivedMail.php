<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public Payment $payment) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Pembayaran pesanan #{$this->order->order_number} berhasil");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.transactional.payment-received');
    }
}
