<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentInitializationAttempt;
use App\Models\StockReservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use App\Exceptions\PaymentInitializationAmbiguousException;

class PaymentService
{
    /** Snap dibuat kedaluwarsa SEBELUM reservasi stok habis, supaya webhook "expire" datang lebih dulu. */
    protected const SNAP_EXPIRY_BUFFER_MINUTES = 1;

    /** Provider requests have a bounded runtime so an expired DB lease cannot race a live request. */
    protected const INITIALIZATION_LEASE_SECONDS = 60;

    public function __construct(
        protected PaymentGatewayInterface $gateway,
        protected OrderService $orderService,
        protected StockReservationService $stockReservationService,
        protected TransactionalEmailService $transactionalEmailService,
    ) {}

    /**
     * Get the active payment gateway instance.
     */
    public function getGateway(): PaymentGatewayInterface
    {
        return $this->gateway;
    }

    /**
     * Siapkan (atau pakai ulang) sesi pembayaran untuk sebuah order.
     *
     * @throws InvalidArgumentException bila order tidak bisa dibayar lagi
     * @throws RuntimeException bila sesi pembayaran tidak bisa dibuat
     */
    public function initializePayment(Order $order, ?string $paymentMethod = null): Payment
    {
        if (! Schema::hasTable('payment_initialization_attempts')) {
            throw new \LogicException(
                'Skema klaim inisialisasi pembayaran belum tersedia. Terapkan migration 2026_10_25_000003_create_payment_initialization_attempts_table sebelum menerima pembayaran.'
            );
        }

        if ($order->status === 'cancelled') {
            throw new InvalidArgumentException('Tidak dapat memproses pembayaran untuk pesanan yang telah dibatalkan.');
        }

        if ($order->payment_status === 'paid') {
            return $order->payments()->where('status', 'paid')->firstOrFail();
        }

        if (in_array($order->payment_status, ['expired', 'refunded'], true)) {
            throw new InvalidArgumentException('Pembayaran untuk pesanan ini sudah tidak dapat dilanjutkan. Silakan buat pesanan baru.');
        }

        // Claim durable under a database row lock. Cache locks are not shared by all
        // Vercel instances when CACHE_STORE=array, so they cannot serialize this operation.
        $claim = $this->claimInitialization($order, $paymentMethod);

        if ($claim instanceof Payment) {
            return $claim;
        }

        try {
            return $this->createSnapSession($order->fresh(), $paymentMethod, $claim);
        } catch (PaymentInitializationAmbiguousException $e) {
            $this->markInitializationAmbiguous($claim);

            report($e);

            throw new \RuntimeException(
                'Status permintaan pembayaran belum dapat dipastikan. '
                .'Jangan mencoba membuat sesi pembayaran baru; '
                .'perlu rekonsiliasi terlebih dahulu.',
                0,
                $e
            );
        } catch (\Throwable $e) {
            $this->markInitializationRetryable($claim);

            report($e);

            throw new \RuntimeException(
                'Gagal menyiapkan pembayaran. Silakan coba lagi beberapa saat.',
                0,
                $e
            );
        }
}

    /**
     * Proses webhook dari payment gateway: verifikasi signature, cek nominal, lalu terapkan perubahan.
     *
     * @param  array<string, mixed>  $payload
     * @return array{status: string, payment: Payment, order: Order}
     */
    public function handleWebhook(array $payload, ?string $signature = null): array
    {
        if (! $this->gateway->verifyWebhook($payload, $signature)) {
            throw new HttpException(400, 'Tanda tangan atau payload webhook tidak valid.');
        }

        $parsed = $this->gateway->parseWebhook($payload);

        $order = Order::where('order_number', $parsed['order_number'])->first();
        if (! $order) {
            throw new HttpException(404, "Pesanan #{$parsed['order_number']} tidak ditemukan.");
        }

        return $this->applyGatewayUpdate($order, $parsed);
    }

    /**
     * Dipanggil scheduler: batalkan order yang melewati batas bayar dan kembalikan stoknya.
     * Sebelum membatalkan, status ditanyakan langsung ke gateway supaya order yang sebenarnya
     * sudah dibayar (webhook terlewat) tidak ikut dibatalkan.
     *
     * @return array{expired: int, confirmed: int, skipped: int}
     */
    public function releaseOverdueOrders(): array
    {
        $cutoff = now()->subMinutes((int) config('midtrans.expiry_grace_minutes', 3));

        $orderIds = OrderItem::query()
            ->whereIn('id', StockReservation::query()
                ->where('status', StockReservation::STATUS_PENDING)
                ->where('expires_at', '<', $cutoff)
                ->whereNotNull('order_item_id')
                ->select('order_item_id'))
            ->distinct()
            ->pluck('order_id');

        $orders = Order::query()
            ->whereIn('id', $orderIds)
            ->where('status', 'pending')
            ->where('payment_status', '!=', 'paid')
            ->get();

        $stats = ['expired' => 0, 'confirmed' => 0, 'skipped' => 0];

        foreach ($orders as $order) {
            try {
                $remote = $this->gateway->fetchStatus($order->order_number);
            } catch (Throwable $e) {
                // Tidak yakin = jangan batalkan. Dicoba lagi pada putaran berikutnya.
                Log::warning('Sweeper: gagal cek status gateway', ['order' => $order->order_number, 'error' => $e->getMessage()]);
                $stats['skipped']++;

                continue;
            }

            if ($remote !== null && $remote['status'] === 'paid') {
                try {
                    $this->applyGatewayUpdate($order, $remote);
                    $stats['confirmed']++;
                } catch (Throwable $e) {
                    Log::error('Sweeper: gagal menerapkan status lunas', ['order' => $order->order_number, 'error' => $e->getMessage()]);
                    $stats['skipped']++;
                }

                continue;
            }

            // Masih menunggu pembayaran di gateway (VA/e-wallet) atau sudah refund: biarkan.
            if ($remote !== null && ! in_array($remote['status'], ['failed', 'expired'], true)) {
                $stats['skipped']++;

                continue;
            }

            if ($this->expireLocally($order, 'Batas waktu pembayaran habis')) {
                $stats['expired']++;
            } else {
                $stats['skipped']++;
            }
        }

        return $stats;
    }

    /**
     * Payment pending/failed terbaru yang token Snap-nya masih berlaku.
     */
    protected function reusablePayment(Order $order): ?Payment
    {
        $payment = $order->payments()
            ->whereIn('status', ['pending', 'failed'])
            ->latest('id')
            ->first();

        if (! $payment) {
            return null;
        }

        $token = $payment->metadata['snap_token'] ?? null;
        $expiresAt = $payment->metadata['token_expires_at'] ?? null;

        if (! $token || ! $expiresAt) {
            return null;
        }

        return Carbon::parse($expiresAt)->isFuture() ? $payment : null;
    }

    protected function claimInitialization(Order $order, ?string $paymentMethod): Payment|PaymentInitializationAttempt
    {
        return DB::transaction(function () use ($order, $paymentMethod) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status === 'cancelled' || in_array($lockedOrder->payment_status, ['expired', 'refunded'], true)) {
                throw new InvalidArgumentException('Pembayaran untuk pesanan ini sudah tidak dapat dilanjutkan.');
            }

            if ($lockedOrder->payment_status === 'paid') {
                return $lockedOrder->payments()->where('status', 'paid')->latest('id')->firstOrFail();
            }

            if ($reusable = $this->reusablePayment($lockedOrder)) {
                return $reusable;
            }

            $requestFingerprint = hash('sha256', json_encode([
                'payment_method' => $paymentMethod,
                'amount' => (string) $lockedOrder->grand_total,
            ], JSON_THROW_ON_ERROR));

            $attempt = PaymentInitializationAttempt::query()
                ->where('order_id', $lockedOrder->id)
                ->latest('attempt_number')
                ->lockForUpdate()
                ->first();

            if ($attempt && $attempt->status === 'ambiguous') {
    if (! hash_equals(
        $attempt->request_fingerprint,
        $requestFingerprint
    )) {
        throw new RuntimeException(
            'Ada permintaan pembayaran sebelumnya yang belum terselesaikan.'
        );
    }

    throw new RuntimeException(
        'Permintaan pembayaran sebelumnya masih perlu direkonsiliasi. '
        .'Sesi baru tidak boleh dibuat otomatis.'
    );
}

            if ($attempt && in_array($attempt->status, ['processing', 'retryable'], true)) {
                if (! hash_equals($attempt->request_fingerprint, $requestFingerprint)) {
                    throw new InvalidArgumentException('Inisialisasi pembayaran sebelumnya memakai metode pembayaran berbeda.');
                }

                if ($attempt->status === 'processing' && $attempt->lease_expires_at?->isFuture()) {
                    throw new RuntimeException('Pembayaran sedang disiapkan. Silakan coba lagi beberapa saat.');
                }

                $attempt->update([
                    'status' => 'processing',
                    'lease_expires_at' => now()->addSeconds(self::INITIALIZATION_LEASE_SECONDS),
                ]);

                return $attempt->fresh();
            }

            $attemptNumber = ($attempt?->attempt_number ?? 0) + 1;

            return PaymentInitializationAttempt::query()->create([
                'order_id' => $lockedOrder->id,
                'attempt_number' => $attemptNumber,
                'idempotency_key' => (string) Str::uuid(),
                'request_fingerprint' => $requestFingerprint,
                'status' => 'processing',
                'lease_expires_at' => now()->addSeconds(self::INITIALIZATION_LEASE_SECONDS),
            ]);
        });
    }

    protected function createSnapSession(Order $order, ?string $paymentMethod, PaymentInitializationAttempt $attempt): Payment
    {
        $secondsLeft = $this->paymentDeadline($order)->getTimestamp() - now()->getTimestamp();
        $expiryMinutes = intdiv($secondsLeft, 60) - self::SNAP_EXPIRY_BUFFER_MINUTES;

        if ($expiryMinutes < 1) {
            throw new RuntimeException('Batas waktu pembayaran pesanan ini sudah habis. Silakan buat pesanan baru.');
        }

        // Never hold a DB transaction or row lock while the provider request is in flight.
        $result = $this->gateway->createPayment($order, [
            'payment_method' => $paymentMethod,
            'expiry_minutes' => $expiryMinutes,
            'idempotency_key' => $attempt->idempotency_key,
        ]);

        $payment = DB::transaction(function () use ($order, $paymentMethod, $result, $attempt) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $lockedAttempt = PaymentInitializationAttempt::query()
                ->whereKey($attempt->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status === 'cancelled'
                || in_array($lockedOrder->payment_status, ['expired', 'refunded'], true)) {
                $lockedAttempt->update(['status' => 'abandoned', 'lease_expires_at' => null]);

                return null;
            }

            if ($lockedOrder->payment_status === 'paid') {
                $payment = $lockedOrder->payments()->where('status', 'paid')->latest('id')->firstOrFail();
                $lockedAttempt->update([
                    'status' => 'completed',
                    'payment_id' => $payment->id,
                    'lease_expires_at' => null,
                ]);

                return $payment;
            }

            $payment = $lockedOrder->payments()
                ->whereIn('status', ['pending', 'failed'])
                ->latest('id')
                ->lockForUpdate()
                ->first();

            $attributes = [
                'provider' => $this->gateway->getName(),
                'payment_method' => $result['payment_method'] ?? $paymentMethod ?? 'midtrans_snap',
                'transaction_reference' => $result['transaction_reference'],
                'amount' => $lockedOrder->grand_total,
                'status' => 'pending',
            ];

            if ($payment) {
                $payment->update($attributes + [
                    'metadata' => array_merge($payment->metadata ?? [], $result['metadata'] ?? []),
                ]);
            } else {
                $payment = $lockedOrder->payments()->create($attributes + [
                    'metadata' => $result['metadata'] ?? [],
                ]);
            }

            $lockedAttempt->update([
                'status' => 'completed',
                'payment_id' => $payment->id,
                'lease_expires_at' => null,
            ]);

            if ($lockedOrder->payment_status === 'failed') {
                $lockedOrder->update(['payment_status' => 'pending']);
            }

            return $payment->fresh();
        });

        if (! $payment) {
            throw new InvalidArgumentException('Pesanan telah berubah dan tidak dapat menerima sesi pembayaran baru.');
        }

        return $payment;
    }

    protected function markInitializationRetryable(PaymentInitializationAttempt $attempt): void
    {
        DB::transaction(function () use ($attempt) {
            $locked = PaymentInitializationAttempt::query()->whereKey($attempt->id)->lockForUpdate()->first();
            if ($locked && $locked->status === 'processing') {
                $locked->update(['status' => 'retryable', 'lease_expires_at' => null]);
            }
        });
    }

    /**
     * Batas akhir pembayaran = waktu kedaluwarsa reservasi stok milik order ini.
     */
    protected function paymentDeadline(Order $order): Carbon
    {
        $reservationExpiry = StockReservation::query()
            ->whereIn('order_item_id', $order->items()->select('id'))
            ->where('status', StockReservation::STATUS_PENDING)
            ->min('expires_at');

        if ($reservationExpiry) {
            return Carbon::parse($reservationExpiry);
        }

        return Carbon::parse($order->created_at)
            ->addMinutes((int) config('midtrans.expiry_minutes', 15));
    }

    /**
     * Terapkan hasil dari gateway (webhook atau fallback status) secara atomik dan idempotent.
     *
     * @param  array<string, mixed>  $parsed  Hasil parseWebhook()/fetchStatus()
     * @return array{status: string, payment: Payment, order: Order}
     */
    protected function applyGatewayUpdate(Order $order, array $parsed): array
    {
        // Verifikasi nominal di sisi server (toleransi Rp1)
        if (abs((float) $parsed['amount'] - (float) $order->grand_total) > 1.0) {
            Log::critical('Pembayaran: nominal tidak cocok dengan tagihan', [
                'order' => $order->order_number,
                'expected' => (float) $order->grand_total,
                'received' => (float) $parsed['amount'],
            ]);

            throw new HttpException(422, 'Jumlah nominal pembayaran tidak sesuai dengan total tagihan pesanan.');
        }

        $result = DB::transaction(function () use ($order, $parsed) {
            // Kunci order lalu payment: webhook ganda / sweeper / halaman bayar diproses satu per satu
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            $payment = $order->payments()->latest('id')->lockForUpdate()->first();

            if (! $payment) {
                $payment = $order->payments()->create([
                    'provider' => $this->gateway->getName(),
                    'payment_method' => $parsed['payment_method'],
                    'transaction_reference' => $parsed['transaction_reference'],
                    'amount' => $order->grand_total,
                    'status' => 'pending',
                    'metadata' => ['raw_webhook' => $parsed['raw']],
                ]);
            }

            $incoming = $parsed['status'];
            $current = $payment->status;

            // Urutan status: lunas dan refund tidak boleh turun ke pending/failed/expired oleh
            // notifikasi yang telat; expired hanya bisa berubah oleh pembayaran telat (paid).
            if ($current === 'refunded'
                || ($current === 'paid' && $incoming !== 'refunded')
                || ($current === 'expired' && $incoming !== 'paid')) {
                return $this->result('already_processed', $payment, $order);
            }

            // Uang masuk setelah pesanan dibatalkan / kedaluwarsa: stok sudah dilepas, jangan dihidupkan
            // kembali. Tandai untuk refund manual.
            $lateOnCancelledOrder = $incoming === 'paid' && $order->status === 'cancelled';

            $metadata = array_merge($payment->metadata ?? [], ['latest_webhook' => $parsed['raw']]);
            if ($lateOnCancelledOrder) {
                $metadata['needs_refund'] = true;
                $metadata['needs_refund_reason'] = 'Pembayaran diterima setelah pesanan dibatalkan atau kedaluwarsa.';
            }

            $update = [
                'status' => $incoming,
                'payment_method' => $parsed['payment_method'] ?: $payment->payment_method,
                'transaction_reference' => $parsed['transaction_reference'] ?: $payment->transaction_reference,
                'metadata' => $metadata,
            ];

            if ($incoming === 'paid') {
                $update['paid_at'] = $parsed['paid_at'] ?? now();
            }

            $payment->update($update);
            $order->update(['payment_status' => $incoming]);

            if ($incoming === 'paid') {
                if ($lateOnCancelledOrder) {
                    Log::critical('Pembayaran terlambat pada pesanan yang sudah dibatalkan, perlu refund', [
                        'order' => $order->order_number,
                        'payment_id' => $payment->id,
                    ]);
                } elseif ($order->status === 'pending') {
                    $order = $this->orderService->transitionStatus(
                        $order,
                        'confirmed',
                        "Pembayaran lunas via {$payment->provider} ({$payment->payment_method})"
                    );
                    $this->stockReservationService->confirmForOrder($order);
                }
            } elseif ($incoming === 'expired' && $order->status === 'pending') {
                // Stok dikembalikan oleh OrderService::cancelOrder (satu-satunya jalur pengembalian stok order)
                $order = $this->orderService->cancelOrder($order, 'Pembayaran kedaluwarsa');
            }

            return $this->result('success', $payment->fresh(), $order->fresh());
        });

        // Only a verified transition into paid reaches this branch. Replayed webhooks
        // return already_processed above, so they cannot send a duplicate confirmation.
        if ($parsed['status'] === 'paid' && $result['status'] === 'success') {
            $this->transactionalEmailService->paymentReceived($result['order'], $result['payment']);
        }

        return $result;
    }

    /**
 * Tanya status ke gateway lalu terapkan. Aman dipanggil berulang (idempotent).
 */
public function syncFromGateway(Order $order): Order
{
    if (in_array($order->payment_status, ['paid', 'refunded'], true)) {
        return $order;
    }

    try {
        $remote = $this->gateway->fetchStatus($order->order_number);
    } catch (Throwable $e) {
        Log::warning('Sync status gagal', ['order' => $order->order_number, 'error' => $e->getMessage()]);

        return $order;
    }

    if ($remote === null) {
        return $order;
    }

    return $this->applyGatewayUpdate($order, $remote)['order'];
}

    /**
     * Batalkan order yang belum dibayar (dipakai sweeper) selama masih pending dan belum lunas.
     */
    protected function expireLocally(Order $order, string $reason): bool
    {
        return DB::transaction(function () use ($order, $reason) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $locked || $locked->status !== 'pending' || $locked->payment_status === 'paid') {
                return false;
            }

            $locked->payments()->whereIn('status', ['pending', 'failed'])->update(['status' => 'expired']);
            $locked->update(['payment_status' => 'expired']);
            $this->orderService->cancelOrder($locked, $reason);

            return true;
        });
    }
protected function markInitializationAmbiguous(
    PaymentInitializationAttempt $claim
): void {
    DB::transaction(function () use ($claim) {
        $attempt = PaymentInitializationAttempt::query()
            ->whereKey($claim->getKey())
            ->lockForUpdate()
            ->first();

        if (! $attempt || $attempt->status !== 'processing') {
            return;
        }

        $attempt->status = 'ambiguous';
        $attempt->lease_expires_at = null;
        $attempt->save();
    });
}


    /**
     * @return array{status: string, payment: Payment, order: Order}
     */
    protected function result(string $status, Payment $payment, Order $order): array
    {
        return ['status' => $status, 'payment' => $payment, 'order' => $order];
    }
}
