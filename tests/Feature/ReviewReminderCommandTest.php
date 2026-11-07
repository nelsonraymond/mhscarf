<?php

namespace Tests\Feature;

use App\Mail\ReviewReminderMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ReviewReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivered_order_younger_than_three_days_is_skipped(): void
    {
        Mail::fake();
        $order = $this->deliveredOrder(deliveredAt: now()->subDays(3)->addMinute());
        OrderItem::factory()->create(['order_id' => $order->id]);

        $this->artisan('reviews:send-reminders')->assertExitCode(0);

        Mail::assertNothingSent();
        $this->assertNull($order->fresh()->review_reminded_at);
    }

    public function test_due_order_sends_one_resend_reminder_with_direct_links_only_for_unreviewed_items(): void
    {
        Mail::fake();
        [$order, $owner, $items] = $this->orderWithItems(2);
        $this->createReview($owner, $items[0]);

        $this->artisan('reviews:send-reminders')->assertExitCode(0);

        Mail::assertSent(ReviewReminderMail::class, function (ReviewReminderMail $mail) use ($order, $owner, $items): bool {
            $rendered = $mail->render();

            return $mail->mailer === 'resend_timed'
                && $mail->hasTo($owner->email)
                && $mail->order->is($order)
                && $mail->items->modelKeys() === [$items[1]->id]
                && str_contains($rendered, route('reviews.create', $items[1]))
                && ! str_contains($rendered, route('reviews.create', $items[0]))
                && $mail->idempotencyKey === 'review-reminder/order/'.$order->id;
        });

        $this->assertNotNull($order->fresh()->review_reminded_at);
    }

    public function test_all_reviewed_order_is_not_sent_a_reminder(): void
    {
        Mail::fake();
        [$order, $owner, $items] = $this->orderWithItems(2);
        foreach ($items as $item) {
            $this->createReview($owner, $item);
        }

        $this->artisan('reviews:send-reminders')->assertExitCode(0);

        Mail::assertNothingSent();
        $this->assertNull($order->fresh()->review_reminded_at);
    }

    public function test_order_without_unreviewed_items_is_skipped_so_the_next_batch_can_run(): void
    {
        Mail::fake();
        config(['services.review_reminders.batch_size' => 1]);
        [$fullyReviewed, $owner, $items] = $this->orderWithItems(1);
        $this->createReview($owner, $items[0]);
        [$next] = $this->orderWithItems(1);

        $this->artisan('reviews:send-reminders')->assertExitCode(0);
        $this->assertNotNull($fullyReviewed->fresh()->review_reminder_skipped_at);
        Mail::assertNothingSent();

        $this->artisan('reviews:send-reminders')->assertExitCode(0);
        Mail::assertSentTimes(ReviewReminderMail::class, 1);
        $this->assertNotNull($next->fresh()->review_reminded_at);
    }

    public function test_invalid_customer_email_is_skipped_so_the_next_batch_can_run(): void
    {
        Mail::fake();
        config(['services.review_reminders.batch_size' => 1]);
        [$invalid] = $this->orderWithItems(1);
        $invalid->user()->update(['email' => 'not-an-email']);
        [$next] = $this->orderWithItems(1);

        $this->artisan('reviews:send-reminders')->assertExitCode(0);
        $this->assertNotNull($invalid->fresh()->review_reminder_skipped_at);
        Mail::assertNothingSent();

        $this->artisan('reviews:send-reminders')->assertExitCode(0);
        Mail::assertSentTimes(ReviewReminderMail::class, 1);
        $this->assertNotNull($next->fresh()->review_reminded_at);
    }

    public function test_cancelled_order_is_not_sent_a_reminder(): void
    {
        Mail::fake();
        $order = $this->deliveredOrder(status: 'cancelled');
        OrderItem::factory()->create(['order_id' => $order->id]);

        $this->artisan('reviews:send-reminders')->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_successfully_reminded_order_is_not_sent_twice(): void
    {
        Mail::fake();
        [$order] = $this->orderWithItems(1);

        $this->artisan('reviews:send-reminders')->assertExitCode(0);
        $this->artisan('reviews:send-reminders')->assertExitCode(0);

        Mail::assertSentTimes(ReviewReminderMail::class, 1);
        $this->assertNotNull($order->fresh()->review_reminded_at);
    }

    public function test_batch_size_limits_orders_and_leaves_the_next_order_for_the_next_run(): void
    {
        Mail::fake();
        config(['services.review_reminders.batch_size' => 1]);
        [$first] = $this->orderWithItems(1);
        [$second] = $this->orderWithItems(1);

        $this->artisan('reviews:send-reminders')->assertExitCode(0);
        Mail::assertSentTimes(ReviewReminderMail::class, 1);
        $this->assertNotNull($first->fresh()->review_reminded_at);
        $this->assertNull($second->fresh()->review_reminded_at);

        $this->artisan('reviews:send-reminders')->assertExitCode(0);
        Mail::assertSentTimes(ReviewReminderMail::class, 2);
        $this->assertNotNull($second->fresh()->review_reminded_at);
    }

   public function test_active_database_claim_prevents_a_second_scheduler_process_from_sending(): void
{
    [$order] = $this->orderWithItems(1);

    $claimToken = (string) Str::uuid();

    $order->update([
        'review_reminder_claimed_at' => now(),
        'review_reminder_claim_token' => $claimToken,
    ]);

    Mail::fake();

    $this->artisan('reviews:send-reminders')->assertExitCode(0);

    Mail::assertNothingSent();

    $freshOrder = $order->fresh();

    $this->assertNull($freshOrder->review_reminded_at);
    $this->assertNotNull($freshOrder->review_reminder_claimed_at);
    $this->assertSame($claimToken, $freshOrder->review_reminder_claim_token);
}

    public function test_failed_delivery_retries_with_the_same_idempotency_key(): void
    {
        [$order] = $this->orderWithItems(1);
        $mailer = Mockery::mock();
        $firstPending = Mockery::mock(PendingMail::class);
        $secondPending = Mockery::mock(PendingMail::class);
        $mailer->shouldReceive('to')->twice()->with($order->user->email)
            ->andReturn($firstPending, $secondPending);
        $payloads = [];
        $originalEmail = $order->user->email;
        $capturePayload = function (ReviewReminderMail $mail) use (&$payloads): bool {
    $payloads[] = [
        $mail->idempotencyKey,
        $mail->render(),
    ];

    return true;
};
        $firstPending->shouldReceive('send')->once()->with(Mockery::on($capturePayload))
            ->andThrow(new RuntimeException('provider failure'));
        $secondPending->shouldReceive('send')->once()->with(Mockery::on($capturePayload));
        $defaultMailer = app('mail.manager')->mailer();

Mail::shouldReceive('mailer')
    ->withNoArgs()
    ->zeroOrMoreTimes()
    ->andReturn($defaultMailer);
    
        Mail::shouldReceive('mailer')->twice()->with('resend_timed')->andReturn($mailer);

        $this->artisan('reviews:send-reminders')->assertExitCode(1);
        $this->assertNull($order->fresh()->review_reminder_claim_token);
        $order->user()->update(['email' => 'changed@example.test']);
        $order->update(['order_number' => 'CHANGED-AFTER-FAILED-ATTEMPT']);
        $order->items()->update(['product_name' => 'Changed product name']);
        $this->travel(22)->hours();
        $this->artisan('reviews:send-reminders')->assertExitCode(0);

        $this->assertNotNull($order->fresh()->review_reminded_at);
        $this->assertCount(2, $payloads);
        $this->assertSame('review-reminder/order/'.$order->id, $payloads[0][0]);
        $this->assertSame($payloads[0][0], $payloads[1][0]);
        $this->assertSame($payloads[0][1], $payloads[1][1]);
    }

    public function test_stale_delivery_outside_resend_idempotency_window_is_abandoned_for_manual_review(): void
    {
        Mail::fake();
        [$order] = $this->orderWithItems(1);
        $order->update([
            'review_reminder_started_at' => now()->subHours(24),
            'review_reminder_item_ids' => [$order->items()->value('id')],
        ]);

        $this->artisan('reviews:send-reminders')->assertExitCode(0);

        Mail::assertNothingSent();
        $this->assertNotNull($order->fresh()->review_reminder_abandoned_at);
    }

    /** @return array{Order, User, list<OrderItem>} */
    private function orderWithItems(int $count): array
    {
        $owner = User::factory()->customer()->create();
        $order = $this->deliveredOrder(owner: $owner);
        $items = [];
        for ($i = 0; $i < $count; $i++) {
            $items[] = OrderItem::factory()->create(['order_id' => $order->id]);
        }

        return [$order->fresh(), $owner, $items];
    }

    private function deliveredOrder(?User $owner = null, ?\Illuminate\Support\Carbon $deliveredAt = null, string $status = 'delivered'): Order
    {
        return Order::factory()->create([
            'user_id' => ($owner ?? User::factory()->customer()->create())->id,
            'status' => $status,
            'delivered_at' => $deliveredAt ?? now()->subDays(3),
        ]);
    }

    private function createReview(User $owner, OrderItem $item): Review
    {
        return Review::create([
            'product_id' => $item->product_id,
            'user_id' => $owner->id,
            'order_item_id' => $item->id,
            'rating' => 5,
            'is_verified_purchase' => true,
            'is_published' => true,
        ]);
    }
}
