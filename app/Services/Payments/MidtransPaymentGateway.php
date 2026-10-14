<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Str;
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

    /**
     * Opsi yang dikenali:
     *  - payment_method   (string)
     *  - expiry_minutes   (int) masa berlaku Snap. Default config('midtrans.expiry_minutes', 15).
     *    Kirim sisa waktu reservasi stok (dikurangi buffer) supaya Midtrans expire LEBIH DULU
     *    daripada reservasi lokal.
     */
    public function createPayment(Order $order, array $options = []): array
    {
        $expiryMinutes = max(1, (int) ($options['expiry_minutes'] ?? config('midtrans.expiry_minutes', 15)));
        $tokenExpiresAt = now()->addMinutes($expiryMinutes);

        // Di environment testing, lewati panggilan Midtrans asli
        if (app()->environment('testing')) {
            return [
                'transaction_reference' => $order->order_number,
                'payment_url' => null,
                'payment_method' => $options['payment_method'] ?? 'midtrans_snap',
                'metadata' => [
                    'snap_token' => 'test_snap_token_'.Str::random(10),
                    'client_key' => $this->clientKey,
                    'is_production' => $this->isProduction,
                    'token_expires_at' => $tokenExpiresAt->toIso8601String(),
                ],
            ];
        }

        if ($this->serverKey === '' || $this->clientKey === '') {
            throw new \RuntimeException('Midtrans configuration error: server key or client key is missing.');
        }

        Config::$serverKey = $this->serverKey;
        Config::$isProduction = $this->isProduction;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Midtrans memakai rupiah bulat. gross_amount dan jumlah item_details HARUS sama persis.
        $grossAmount = (int) round((float) $order->grand_total);

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $order->shipping_recipient_name,
                'email' => $order->user?->email,
                'phone' => $order->shipping_phone,
            ],
            'item_details' => [
                [
                    'id' => $order->order_number,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => 'Order '.$order->order_number,
                ],
            ],

            // Tanpa ini Snap berlaku 24 jam, sementara reservasi stok hanya 15 menit.
            'expiry' => [
                'unit' => 'minutes',
                'duration' => $expiryMinutes,
            ],

            'callbacks' => [
            'finish' => url('/orders/'.$order->id.'/payment'),
            ],
        ];

        try {
            $transaction = Snap::createTransaction($params);
                $snapToken = $transaction->token ?? null;
                $redirectUrl = $transaction->redirect_url ?? null;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to obtain Midtrans Snap token: '.$e->getMessage());
        }

        return [
            'transaction_reference' => $order->order_number,
            'payment_url' => $redirectUrl ?? null,
            'payment_method' => $options['payment_method'] ?? 'midtrans_snap',
            'metadata' => [
                'snap_token' => $snapToken,
                'client_key' => $this->clientKey,
                'is_production' => $this->isProduction,
                // Dipakai PaymentService untuk memutuskan apakah token boleh dipakai ulang
                'token_expires_at' => $tokenExpiresAt->toIso8601String(),
                'redirect_url' => $redirectUrl ?? null,
            ],
        ];
    }

    public function verifyWebhook(array $payload, ?string $signature = null): bool
    {
        // Server key kosong berarti signature bisa dihitung siapa saja. Tolak.
        if ($this->serverKey === '') {
            return false;
        }

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

    /**
     * Ambil status transaksi langsung dari Midtrans (fallback bila webhook terlewat).
     * Mengembalikan data ternormalisasi seperti parseWebhook(), atau null bila Midtrans
     * belum punya transaksi untuk order ini (customer belum memilih metode bayar).
     *
     * @throws \RuntimeException bila Midtrans tidak dapat dihubungi
     */
    public function fetchStatus(string $orderNumber): ?array
    {
        if ($this->serverKey === '') {
            throw new \RuntimeException('Midtrans configuration error: server key is missing.');
        }

        Config::$serverKey = $this->serverKey;
        Config::$isProduction = $this->isProduction;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        try {
            $status = \Midtrans\Transaction::status($orderNumber);
        } catch (\Exception $e) {
            if ((int) $e->getCode() === 404) {
                return null;
            }

            throw new \RuntimeException('Gagal mengambil status Midtrans: '.$e->getMessage(), 0, $e);
        }

        return $this->parseWebhook((array) $status);
    }

    public function parseWebhook(array $payload): array
    {
        $transactionStatus = $payload['transaction_status'] ?? 'pending';
        $fraudStatus = $payload['fraud_status'] ?? null;

        $normalizedStatus = match ($transactionStatus) {
            'settlement' => 'paid',
            'capture' => match ($fraudStatus) {
                'accept', null => 'paid',
                // challenge = menunggu review fraud, BUKAN gagal. Nanti Midtrans kirim accept/deny.
                'challenge' => 'pending',
                default => 'failed',
            },
            'pending' => 'pending',
            'deny', 'cancel', 'failure' => 'failed',
            'expire' => 'expired',
            'refund', 'partial_refund' => 'refunded',
            default => 'pending',
        };

        $paidAt = null;
        if ($normalizedStatus === 'paid') {
            // settlement_time dari Midtrans berformat WIB tanpa zona waktu
            $paidAt = isset($payload['settlement_time'])
                ? Carbon::parse($payload['settlement_time'], 'Asia/Jakarta')->setTimezone(config('app.timezone'))
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