<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShipmentDispatchedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public Shipment $shipment) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Pesanan #{$this->order->order_number} sedang dikirim");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.transactional.shipment-dispatched');
    }
}
