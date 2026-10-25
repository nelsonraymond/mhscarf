<?php

namespace App\Contracts;

use App\Models\Order;
use Carbon\CarbonInterface;

interface PaymentGatewayInterface
{
    /**
     * Get the identifier name of the payment gateway.
     */
    public function getName(): string;

    /**
     * Initialize/create a transaction session with the payment gateway.
     *
     * Opsi yang dikenali: payment_method (string), expiry_minutes (int), idempotency_key (string).
     *
     * @param  array<string, mixed>  $options
     * @return array{
     *     transaction_reference: string,
     *     payment_url?: ?string,
     *     payment_method?: ?string,
     *     metadata?: array<string, mixed>
     * }
     */
    public function createPayment(Order $order, array $options = []): array;

    /**
     * Verify whether a webhook/callback payload is authentic and untampered.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verifyWebhook(array $payload, ?string $signature = null): bool;

    /**
     * Parse and normalize a webhook payload into standard gateway-agnostic format.
     *
     * @param  array<string, mixed>  $payload
     * @return array{
     *     order_number: string,
     *     transaction_reference: string,
     *     status: 'pending'|'paid'|'failed'|'expired'|'refunded',
     *     payment_method: ?string,
     *     amount: float,
     *     paid_at: ?CarbonInterface,
     *     raw: array<string, mixed>
     * }
     */
    public function parseWebhook(array $payload): array;

    /**
     * Ambil status transaksi langsung dari gateway (fallback bila webhook terlewat).
     * Hasilnya sama dengan parseWebhook(), atau null bila gateway belum punya
     * transaksi untuk order ini.
     *
     * @return array{
     *     order_number: string,
     *     transaction_reference: string,
     *     status: 'pending'|'paid'|'failed'|'expired'|'refunded',
     *     payment_method: ?string,
     *     amount: float,
     *     paid_at: ?CarbonInterface,
     *     raw: array<string, mixed>
     * }|null
     *
     * @throws \RuntimeException bila gateway tidak dapat dihubungi
     */
    public function fetchStatus(string $orderNumber): ?array;
}
