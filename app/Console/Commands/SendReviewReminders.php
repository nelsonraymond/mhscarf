<?php

namespace App\Console\Commands;

use App\Mail\ReviewReminderMail;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class SendReviewReminders extends Command
{
    protected $signature = 'reviews:send-reminders';

    protected $description = 'Kirim batch pengingat ulasan untuk pesanan yang memenuhi syarat';

    private const CLAIM_LEASE_MINUTES = 15;

    // Hobby cron runs daily; this window only permits a manual rerun to reuse the original key.
    // Abandon retries before Resend's 24-hour key retention expires.
    private const IDEMPOTENCY_WINDOW_HOURS = 23;

    public function handle(): int
    {
        $sent = 0;
        $failed = false;
        $batchSize = max(1, (int) config('services.review_reminders.batch_size', 1));
        $orderIds = Order::query()
            ->whereIn('status', ['delivered', 'completed'])
            ->whereNotNull('delivered_at')
            ->where('delivered_at', '<=', now()->subDays(3))
            ->whereNull('review_reminded_at')
            ->whereNull('review_reminder_abandoned_at')
            ->whereNull('review_reminder_skipped_at')
            ->whereNotNull('user_id')
            ->where(function ($query): void {
                $query->whereNull('review_reminder_claimed_at')
                    ->orWhere('review_reminder_claimed_at', '<=', now()->subMinutes(self::CLAIM_LEASE_MINUTES));
            })
            ->orderBy('id')
            ->limit($batchSize)
            ->pluck('id');

        foreach ($orderIds as $orderId) {
            $claim = $this->claim((int) $orderId);
            if (! $claim) {
                continue;
            }

            try {
                $items = $claim['order']->items
                    ->whereIn('id', $claim['item_ids'])
                    ->values();

                $mail = new ReviewReminderMail(
                    $claim['order'],
                    $items,
                    'review-reminder/order/'.$claim['order']->id,
                );
                $payload = $claim['payload'] ?? [
                    'recipient' => $claim['order']->user->email,
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name'),
                    'subject' => $mail->envelope()->subject,
                    'html' => $mail->render(),
                ];
                $payload = $this->persistPayload($claim, $payload);
                if ($payload === null) {
                    continue;
                }

                Mail::mailer('resend_timed')
    ->to($payload['recipient'])
    ->send((new ReviewReminderMail(
        $claim['order'],
        $items,
        'review-reminder/order/'.$claim['order']->id,
        $payload,
    ))->from($payload['from_address'], $payload['from_name']));

                DB::transaction(function () use ($claim): void {
                    $order = Order::query()->whereKey($claim['order']->id)->lockForUpdate()->first();
                    if ($order && $order->review_reminder_claim_token === $claim['token']) {
                        $order->update([
                            'review_reminded_at' => now(),
                            'review_reminder_claimed_at' => null,
                            'review_reminder_claim_token' => null,
                        ]);
                    }
                });

                $sent++;
            } catch (Throwable $exception) {
                $failed = true;
                DB::transaction(function () use ($claim): void {
                    $order = Order::query()->whereKey($claim['order']->id)->lockForUpdate()->first();
                    if ($order && $order->review_reminder_claim_token === $claim['token']) {
                        $order->update([
                            'review_reminder_claimed_at' => null,
                            'review_reminder_claim_token' => null,
                        ]);
                    }
                });

                Log::warning('Review reminder delivery failed', [
                    'order_id' => $claim['order']->id,
                    'exception' => $exception::class,
                ]);
            }
        }

        $this->info("Pengingat terkirim: {$sent}");

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Atomically reserve an eligible order. The row lock serializes independent scheduler
     * instances; the claim token prevents an expired worker from finalizing a newer claim.
     *
     * @return array{order: Order, item_ids: list<int>, payload: ?array, token: string}|null
     */
    private function claim(int $orderId): ?array
    {
        return DB::transaction(function () use ($orderId): ?array {
            $order = Order::query()->whereKey($orderId)->lockForUpdate()->first();
            if (! $order
                || ! in_array($order->status, ['delivered', 'completed'], true)
                || ! $order->delivered_at
                || $order->delivered_at->gt(now()->subDays(3))
                || $order->review_reminded_at
                || $order->review_reminder_abandoned_at
                || $order->review_reminder_skipped_at
                || ! $order->user_id) {
                return null;
            }

            $leaseActive = $order->review_reminder_claimed_at
                && $order->review_reminder_claimed_at->gt(now()->subMinutes(self::CLAIM_LEASE_MINUTES));
            if ($leaseActive) {
                return null;
            }

            if ($order->review_reminder_started_at
                && $order->review_reminder_started_at->lte(now()->subHours(self::IDEMPOTENCY_WINDOW_HOURS))) {
                $order->update(['review_reminder_abandoned_at' => now()]);
                Log::warning('Review reminder requires manual review after provider idempotency window', [
                    'order_id' => $order->id,
                ]);

                return null;
            }

            $order->loadMissing('user');
            if (! $order->user || ! filter_var($order->user->email, FILTER_VALIDATE_EMAIL)) {
                $order->update(['review_reminder_skipped_at' => now()]);
                Log::info('Review reminder skipped because customer email is invalid', [
                    'order_id' => $order->id,
                ]);

                return null;
            }

            $itemIds = $order->review_reminder_item_ids;
            if (! is_array($itemIds)) {
                $allItemIds = $order->items()->pluck('id');
                $reviewedIds = Review::withTrashed()
                    ->whereIn('order_item_id', $allItemIds)
                    ->pluck('order_item_id');
                $itemIds = $allItemIds->diff($reviewedIds)->map(fn ($id): int => (int) $id)->values()->all();

                if ($itemIds === []) {
                    $order->update(['review_reminder_skipped_at' => now()]);
                    Log::info('Review reminder skipped because no unreviewed items remain', [
                        'order_id' => $order->id,
                    ]);

                    return null;
                }

                $order->review_reminder_started_at = now();
                $order->review_reminder_item_ids = $itemIds;
            }

            $token = (string) Str::uuid();
            $order->review_reminder_claimed_at = now();
            $order->review_reminder_claim_token = $token;
            $order->save();
            $order->load(['user', 'items']);

            return [
                'order' => $order,
                'item_ids' => $itemIds,
                'payload' => $order->review_reminder_payload,
                'token' => $token,
            ];
        });
    }

    /** @param array{order: Order, item_ids: list<int>, payload: ?array, token: string} $claim
     *  @param array{recipient: string, from_address: string, from_name: string, subject: string, html: string} $payload
     *  @return array{recipient: string, from_address: string, from_name: string, subject: string, html: string}|null
     */
    private function persistPayload(array $claim, array $payload): ?array
    {
        return DB::transaction(function () use ($claim, $payload): ?array {
            $order = Order::query()->whereKey($claim['order']->id)->lockForUpdate()->first();
            if (! $order || $order->review_reminder_claim_token !== $claim['token']) {
                return null;
            }

            if (is_array($order->review_reminder_payload)) {
                return $order->review_reminder_payload;
            }

            $order->update(['review_reminder_payload' => $payload]);

            return $payload;
        });
    }
}
