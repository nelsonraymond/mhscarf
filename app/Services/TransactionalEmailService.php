<?php

namespace App\Services;

use App\Mail\OrderPlacedMail;
use App\Mail\PaymentReceivedMail;
use App\Mail\ShipmentDispatchedMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TransactionalEmailService
{
    public function orderPlaced(Order $order): void
    {
        $order->loadMissing(['user', 'items']);
        $this->send($order, new OrderPlacedMail($order), 'order_placed');
    }

    public function paymentReceived(Order $order, Payment $payment): void
    {
        $order->loadMissing(['user', 'items']);
        $this->send($order, new PaymentReceivedMail($order, $payment), 'payment_received');
    }

    public function shipmentDispatched(Order $order, Shipment $shipment): void
    {
        $order->loadMissing(['user', 'items']);
        $this->send($order, new ShipmentDispatchedMail($order, $shipment), 'shipment_dispatched');
    }

    private function send(Order $order, Mailable $mail, string $type): void
    {
        $recipient = $order->user;

        if (! $recipient || ! filter_var($recipient->email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Transactional email skipped: owner email unavailable', [
                'order_id' => $order->id,
                'email_type' => $type,
            ]);

            return;
        }

        try {
            Mail::to($recipient->email)->send($mail);
        } catch (Throwable $exception) {
            // Keep checkout, payment confirmation and fulfillment independent of email delivery.
            // Avoid logging exception messages because SDK errors may contain request details.
            Log::warning('Transactional email delivery failed', [
                'order_id' => $order->id,
                'email_type' => $type,
                'exception' => $exception::class,
            ]);
        }
    }
}
