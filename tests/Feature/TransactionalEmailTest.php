<?php

namespace Tests\Feature;

use App\Mail\OrderPlacedMail;
use App\Mail\PaymentReceivedMail;
use App\Mail\ShipmentDispatchedMail;
use App\Mail\Transport\TimedResendTransport;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shipment;
use App\Models\StockReservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Mockery;
use RuntimeException;
use Tests\TestCase;
use Tests\Concerns\InteractsWithShippingQuotes;

class TransactionalEmailTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithShippingQuotes;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'midtrans.server_key' => 'test-server-key',
            'midtrans.client_key' => 'test-client-key',
        ]);
    }

    public function test_resend_mailer_builds_the_transport_with_the_configured_driver(): void
    {
        config(['services.resend.key' => 'test-resend-key']);

        $transport = Mail::mailer('resend')->getSymfonyTransport();

        $this->assertInstanceOf(TimedResendTransport::class, $transport);
    }

    public function test_checkout_sends_owner_order_email_once_for_a_retried_checkout_token(): void
    {
        Mail::fake();
        [$customer, $address, $variant] = $this->prepareCart();
        $payload = [
            'address_id' => $address->id,
            'shipping_quote_token' => $this->fakeShippingQuote(),
            'checkout_token' => 'transactional-email-order-once',
        ];

        $first = $this->actingAs($customer)->postJson(route('checkout.store'), $payload)->assertOk();
        $this->actingAs($customer)->postJson(route('checkout.store'), $payload)->assertOk();
        $order = Order::with('items')->findOrFail($first->json('order_id'));

        Mail::assertSent(OrderPlacedMail::class, function (OrderPlacedMail $mail) use ($order, $customer): bool {
            $mail->assertSeeInHtml($order->order_number);
            $mail->assertSeeInHtml('Pashmina Silk');
            $mail->assertSeeInHtml('Jl. Mawar No. 10');

            return $mail->order->is($order) && $mail->hasTo($customer->email);
        });
        Mail::assertSentCount(1);
        $this->assertSame(9, $variant->fresh()->stock_qty);
    }

    public function test_checkout_is_not_rolled_back_when_order_email_delivery_fails(): void
    {
        [$customer, $address, $variant] = $this->prepareCart();
        Mail::shouldReceive('to')
            ->once()
            ->with($customer->email)
            ->andThrow(new RuntimeException('Simulated Resend failure'));

        $response = $this->actingAs($customer)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => $this->fakeShippingQuote(),
        ]);

        $response->assertRedirect();
        $this->assertSame(1, Order::where('user_id', $customer->id)->count());
        $this->assertSame(9, $variant->fresh()->stock_qty);
    }

    public function test_verified_payment_sends_one_confirmation_for_replayed_webhook(): void
    {
        Mail::fake();
        [$order, $owner] = $this->createOrderWithPayment();
        $payload = $this->signedPayload($order, 'settlement');

        $this->postJson(route('payments.webhook'), $payload)->assertOk();
        $this->postJson(route('payments.webhook'), $payload)->assertOk();

        Mail::assertSent(PaymentReceivedMail::class, function (PaymentReceivedMail $mail) use ($order, $owner): bool {
            $mail->assertSeeInHtml($order->order_number);
            $mail->assertSeeInHtml('Rp 165.000');

            return $mail->order->is($order) && $mail->hasTo($owner->email) && $mail->payment->status === 'paid';
        });
        Mail::assertSentCount(1);
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_pending_payment_does_not_send_success_email(): void
    {
        Mail::fake();
        [$order] = $this->createOrderWithPayment();

        $this->postJson(route('payments.webhook'), $this->signedPayload($order, 'pending'))->assertOk();

        Mail::assertNothingSent();
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_failed_payment_does_not_send_success_email(): void
    {
        Mail::fake();
        [$order] = $this->createOrderWithPayment();

        $this->postJson(route('payments.webhook'), $this->signedPayload($order, 'deny'))->assertOk();

        Mail::assertNothingSent();
        $this->assertSame('failed', $order->fresh()->payment_status);
    }

    public function test_payment_state_and_reserved_stock_are_preserved_when_payment_email_fails(): void
    {
        [$order, $owner] = $this->createOrderWithPayment();
        $orderItem = $order->items()->firstOrFail();
        $variant = ProductVariant::findOrFail($orderItem->product_variant_id);
        $variant->decrement('stock_qty', $orderItem->quantity);
        $reservation = StockReservation::create([
            'product_variant_id' => $variant->id,
            'order_item_id' => $orderItem->id,
            'quantity' => $orderItem->quantity,
            'status' => StockReservation::STATUS_PENDING,
            'expires_at' => now()->addMinutes(15),
        ]);
        Mail::shouldReceive('to')
            ->once()
            ->with($owner->email)
            ->andThrow(new RuntimeException('Simulated Resend failure'));

        $this->postJson(route('payments.webhook'), $this->signedPayload($order, 'settlement'))->assertOk();

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('paid', $order->payments()->latest('id')->firstOrFail()->status);
        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertSame(8, $variant->fresh()->stock_qty);
        $this->assertSame(StockReservation::STATUS_RESERVED, $reservation->fresh()->status);
    }

    public function test_another_customer_cannot_trigger_payment_email_for_an_order(): void
    {
        Mail::fake();
        [$order] = $this->createOrderWithPayment();
        $otherCustomer = User::factory()->customer()->create();

        $this->actingAs($otherCustomer)->get(route('payments.show', $order))->assertForbidden();

        Mail::assertNothingSent();
    }

    public function test_shipped_email_is_sent_once_when_order_and_shipment_reach_shipped_status(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        [$order, $owner] = $this->createShippableOrder('TRACK-12345');

        // Shipment data is saved first; the order transition then dispatches exactly once.
        $this->actingAs($admin)->patch(route('admin.orders.shipment', $order), [
            'courier' => 'JNE',
            'service' => 'REG',
            'tracking_number' => 'TRACK-12345',
        ])->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'shipped'])->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.orders.shipment', $order), [
            'courier' => 'JNE', 'service' => 'REG', 'tracking_number' => 'TRACK-12345',
        ])->assertRedirect();

        Mail::assertSent(ShipmentDispatchedMail::class, function (ShipmentDispatchedMail $mail) use ($order, $owner): bool {
            $mail->assertSeeInHtml('JNE');
            $mail->assertSeeInHtml('REG');
            $mail->assertSeeInHtml('TRACK-12345');

            return $mail->order->is($order) && $mail->hasTo($owner->email);
        });
        Mail::assertSentCount(1);
    }

    public function test_order_cannot_be_marked_shipped_without_tracking_number(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        [$order, $owner] = $this->createShippableOrder(null);
        $this->actingAs($admin)->patch(route('admin.orders.shipment', $order), [
            'courier' => 'JNE',
            'service' => 'REG',
            'tracking_number' => null,
        ])->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'shipped'])
            ->assertSessionHas('error');
        $this->assertSame('packed', $order->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_shipment_email_failure_does_not_roll_back_shipped_transition(): void
    {
        $admin = User::factory()->admin()->create();
        [$order] = $this->createShippableOrder('TRACK-FAIL-1');
        Mail::shouldReceive('to')
            ->once()
            ->with($order->user->email)
            ->andThrow(new RuntimeException('Simulated Resend failure'));

        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'shipped'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('shipped', $order->fresh()->status);
        $this->assertSame('shipped', $order->shipment->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'packed',
            'to_status' => 'shipped',
            'actor_id' => $admin->id,
        ]);
    }

    public function test_order_email_is_sent_after_checkout_transaction_finishes(): void
    {
        [$customer, $address, $variant] = $this->prepareCart();
        $transactionLevelBeforeRequest = DB::transactionLevel();
        $pendingMail = Mockery::mock(\Illuminate\Mail\PendingMail::class);
        $pendingMail->shouldReceive('send')->once()->withArgs(function (OrderPlacedMail $mail) use ($customer, $transactionLevelBeforeRequest, $variant): bool {
            $this->assertSame($transactionLevelBeforeRequest, DB::transactionLevel());
            $this->assertDatabaseHas('orders', ['id' => $mail->order->id]);
            $this->assertSame(9, $variant->fresh()->stock_qty);
            $this->assertSame($customer->id, $mail->order->user_id);

            return true;
        });
        Mail::shouldReceive('to')->once()->with($customer->email)->andReturn($pendingMail);

        $this->actingAs($customer)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => $this->fakeShippingQuote(),
        ])->assertRedirect();
    }

    public function test_shipment_email_waits_until_both_order_and_shipment_are_shipped(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        [$order] = $this->createShippableOrder('TRACK-54321');

        $this->actingAs($admin)->patch(route('admin.orders.shipment', $order), [
            'courier' => 'JNE', 'service' => 'REG', 'tracking_number' => 'TRACK-54321',
        ])->assertRedirect();
        Mail::assertNothingSent();

        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'shipped'])->assertRedirect();
        Mail::assertSent(ShipmentDispatchedMail::class, 1);
        Mail::assertSentCount(1);
    }
    /** @return array{User, Address, ProductVariant} */
    private function prepareCart(): array
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant();
        $address = Address::factory()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Aisyah Putri',
            'address_line' => 'Jl. Mawar No. 10',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
        ]);
        $cart = Cart::create(['user_id' => $customer->id]);
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 75000,
        ]);

        return [$customer, $address, $variant];
    }

    /** @return array{Order, User} */
    private function createOrderWithPayment(): array
    {
        $owner = User::factory()->customer()->create();
        $order = Order::factory()->create([
            'user_id' => $owner->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 150000,
            'shipping_cost' => 15000,
            'grand_total' => 165000,
        ]);
        $variant = $this->createVariant();
        $order->items()->create([
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'variant_name' => $variant->name,
            'sku' => $variant->sku,
            'unit_price' => 75000,
            'quantity' => 2,
            'subtotal' => 150000,
        ]);
        Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 165000,
            'status' => 'pending',
        ]);

        return [$order->fresh(), $owner];
    }

    /** @return array{Order, User} */
    private function createShippableOrder(?string $trackingNumber): array
    {
        $owner = User::factory()->customer()->create();
        $order = Order::factory()->create([
            'user_id' => $owner->id,
            'status' => 'packed',
            'payment_status' => 'paid',
        ]);
        Shipment::factory()->create([
            'order_id' => $order->id,
            'courier' => 'JNE',
            'service' => 'REG',
            'tracking_number' => $trackingNumber,
            'status' => 'pending',
        ]);

        return [$order->fresh(), $owner];
    }

    private function createVariant(): ProductVariant
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Pashmina Silk',
            'base_price' => 75000,
            'is_active' => true,
        ]);

        return ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Mocca',
            'sku' => 'SKU-'.strtoupper(fake()->lexify('????')).'-01',
            'stock_qty' => 10,
            'additional_price' => 0,
            'is_active' => true,
        ]);
    }

    /** @return array<string, mixed> */
    private function signedPayload(Order $order, string $transactionStatus): array
    {
        $statusCode = match ($transactionStatus) {
            'settlement', 'capture' => '200',
            'deny', 'cancel' => '400',
            'expire' => '407',
            default => '201',
        };
        $gross = number_format((float) $order->grand_total, 2, '.', '');

        return [
            'order_id' => $order->order_number,
            'status_code' => $statusCode,
            'gross_amount' => $gross,
            'signature_key' => hash('sha512', $order->order_number.$statusCode.$gross.config('midtrans.server_key')),
            'transaction_id' => 'TRX-'.strtoupper(fake()->bothify('????#####')),
            'transaction_status' => $transactionStatus,
            'payment_type' => 'bank_transfer',
            'settlement_time' => now()->toDateTimeString(),
        ];
    }
}



