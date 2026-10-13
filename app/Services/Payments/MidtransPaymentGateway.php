<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransPaymentGateway implements PaymentGatewayInterface
{
    protected string $serverKey;

    protected string $clientKey;

    protected bool $isProduction;

    public function __construct()
    {
        $this->serverKey = (string) config('midtrans.server_key', '');
        $this->clientKey = (string) config('midtrans.client_key', '');
        $this->isProduction = (bool) config('midtrans.is_production', false);
    }

    public function getName(): string
    {
        return 'midtrans';
    }

    public function createPayment(Order $order, array $options = []): array
    {
        // Force sandbox mode
        $this->isProduction = false;

        // In testing environment, bypass real Midtrans call and return dummy token
        if (app()->environment('testing')) {
            return [
                'transaction_reference' => $order->order_number,
                'payment_url' => null,
                'payment_method' => $options['payment_method'] ?? 'midtrans_snap',
                'metadata' => [
                    'snap_token' => 'test_snap_token_'.\Str::random(10),
                    'client_key' => $this->clientKey,
                    'is_production' => false,
                ],
            ];
        }
        // Ensure sandbox credentials are present; abort if missing
        if (empty($this->serverKey) || empty($this->clientKey)) {
            throw new \RuntimeException('Midtrans configuration error: server key or client key is missing.');
        }

        // Configure Midtrans SDK for sandbox
        Config::$serverKey = $this->serverKey;
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Build payload for Snap token
        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (float) $order->grand_total,
                'currency' => 'IDR',
            ],
            'customer_details' => [
                'first_name' => $order->shipping_recipient_name,
                'email' => $order->user?->email,
                'phone' => $order->shipping_phone,
            ],
            // Midtrans requires at least one item detail; provide a simple aggregate item
            'item_details' => [
                [
                    'id' => $order->order_number,
                    'price' => (int) $order->grand_total,
                    'quantity' => 1,
                    'name' => 'Order '.$order->order_number,
                ],
            ],
        ];
        // SSL verification is enforced. Ensure a valid CA bundle is configured via php.ini (curl.cainfo).

        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Exception $e) {
            // Provide a clear error for debugging
            throw new \RuntimeException('Failed to obtain Midtrans Snap token: '.$e->getMessage());
        }

        return [
            'transaction_reference' => $order->order_number,
            'payment_url' => null,
            'payment_method' => $options['payment_method'] ?? 'midtrans_snap',
            'metadata' => [
                'snap_token' => $snapToken,
                'client_key' => $this->clientKey,
                'is_production' => false,
            ],
        ];
    }

    public function verifyWebhook(array $payload, ?string $signature = null): bool
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $receivedSignature = $signature ?? (string) ($payload['signature_key'] ?? '');

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $receivedSignature === '') {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        return hash_equals($expectedSignature, $receivedSignature);
    }

    public function parseWebhook(array $payload): array
    {
        $transactionStatus = $payload['transaction_status'] ?? 'pending';
        $fraudStatus = $payload['fraud_status'] ?? null;

        $normalizedStatus = match ($transactionStatus) {
            'settlement' => 'paid',
            'capture' => ($fraudStatus === 'accept' || $fraudStatus === null) ? 'paid' : 'failed',
            'pending' => 'pending',
            'deny', 'cancel' => 'failed',
            'expire' => 'expired',
            'refund', 'partial_refund' => 'refunded',
            default => 'pending',
        };

        $paidAt = null;
        if ($normalizedStatus === 'paid') {
            $paidAt = isset($payload['settlement_time'])
                ? Carbon::parse($payload['settlement_time'])
                : now();
        }

        return [
            'order_number' => (string) ($payload['order_id'] ?? ''),
            'transaction_reference' => (string) ($payload['transaction_id'] ?? ($payload['order_id'] ?? '')),
            'status' => $normalizedStatus,
            'payment_method' => (string) ($payload['payment_type'] ?? 'midtrans'),
            'amount' => (float) ($payload['gross_amount'] ?? 0),
            'paid_at' => $paidAt,
            'raw' => $payload,
        ];
    }
}
