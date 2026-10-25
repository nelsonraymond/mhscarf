<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentInitializationAttempt;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockReservation;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Contracts\PaymentGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use Tests\Concerns\InteractsWithShippingQuotes;

class PaymentTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithShippingQuotes;

    protected function setUp(): void
    {
        parent::setUp();

        // Gateway menolak webhook bila server key kosong, jadi tes memakai key sendiri.
        config([
            'midtrans.server_key' => 'test-server-key',
            'midtrans.client_key' => 'test-client-key',
        ]);
    }

    /**
     * Build a signed Midtrans-style webhook payload for the given order.
     *
     * @return array<string, mixed>
     */
    protected function signedPayload(Order $order, string $transactionStatus = 'settlement', ?float $grossAmount = null): array
    {
        $statusCode = match ($transactionStatus) {
            'settlement' => '200',
            'capture' => '200',
            'deny', 'cancel' => '400',
            'expire' => '407',
            'refund' => '200',
            default => '201',
        };

        $gross = number_format($grossAmount ?? (float) $order->grand_total, 2, '.', '');
        $serverKey = (string) config('midtrans.server_key', '');

        return [
            'order_id' => $order->order_number,
            'status_code' => $statusCode,
            'gross_amount' => $gross,
            'signature_key' => hash('sha512', $order->order_number.$statusCode.$gross.$serverKey),
            'transaction_id' => 'TRX-'.strtoupper(fake()->bothify('????#####')),
            'transaction_status' => $transactionStatus,
            'payment_type' => 'bank_transfer',
            'settlement_time' => now()->toDateTimeString(),
        ];
    }

    protected function createOrderWithItems(?User $user = null, int $stock = 10, int $quantity = 2): Order
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'base_price' => 75000,
            'is_active' => true,
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'SKU-'.strtoupper(fake()->lexify('????')).'-01',
            'additional_price' => 0,
            'stock_qty' => $stock,
            'is_active' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => ($user ?? User::factory()->customer()->create())->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 150000,
            'shipping_cost' => 15000,
            'grand_total' => 165000,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'sku' => $variant->sku,
            'unit_price' => 75000,
            'quantity' => $quantity,
            'subtotal' => 75000 * $quantity,
        ]);

        return $order->fresh();
    }

    /**
     * Buat order lewat alur checkout sungguhan, sehingga stok terpotong dan reservasi terhubung ke order.
     *
     * @return array{0: Order, 1: ProductVariant, 2: User}
     */
    protected function placeRealOrder(int $stock = 10, int $quantity = 2): array
    {
        $variant = ProductVariant::factory()->create(['stock_qty' => $stock]);
        $user = User::factory()->customer()->create();
        $user->addresses()->create([
            'recipient_name' => 'John Doe',
            'phone' => '08123456789',
            'address_line' => 'Jl. Example 123',
            'city' => 'Jakarta',
            'province' => 'Jakarta',
            'postal_code' => '12345',
            'is_default' => true,
        ]);

        $cart = $this->app->make(CartService::class)->getOrCreateCart($user, session()->getId());
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
            'unit_price' => $variant->finalPrice(),
        ]);

        $order = $this->app->make(CheckoutService::class)->placeOrder($user, [
            'address_id' => $user->addresses()->first()->id,
            'checkout_token' => (string) \Illuminate\Support\Str::uuid(),
            'shipping_quote' => $this->internalShippingQuote(weight: (int) $variant->weight_gram * $quantity),
        ]);

        return [$order, $variant, $user];
    }

    public function test_payment_page_loads_for_own_order(): void
    {
        $customer = User::factory()->customer()->create();
        $order = $this->createOrderWithItems($customer);

        $response = $this->actingAs($customer)->get(route('payments.show', $order));

        $response->assertOk();
        $response->assertSee($order->order_number);
    }

    public function test_customer_cannot_open_another_customers_payment_page(): void
    {
        $owner = User::factory()->customer()->create();
        $intruder = User::factory()->customer()->create();
        $order = $this->createOrderWithItems($owner);

        $response = $this->actingAs($intruder)->get(route('payments.show', $order));

        $response->assertForbidden();
    }

    public function test_payment_initialization_creates_or_reuses_pending_payment(): void
    {
        $customer = User::factory()->customer()->create();
        $order = $this->createOrderWithItems($customer);

        // First visit initializes a pending payment record
        $this->actingAs($customer)->get(route('payments.show', $order))->assertOk();
        $this->assertSame(1, $order->payments()->where('status', 'pending')->count());

        // Second visit reuses it instead of duplicating
        $this->actingAs($customer)->get(route('payments.show', $order))->assertOk();
        $this->assertSame(1, $order->payments()->where('status', 'pending')->count());

        $payment = $order->payments()->first();
        $this->assertSame('midtrans', $payment->provider);
        $this->assertEquals(165000, (float) $payment->amount);
    }

    public function test_snap_token_is_reused_while_still_valid(): void
    {
        $customer = User::factory()->customer()->create();
        $order = $this->createOrderWithItems($customer);

        $this->actingAs($customer)->get(route('payments.show', $order))->assertOk();
        $firstToken = $order->payments()->first()->metadata['snap_token'];

        $this->actingAs($customer)->get(route('payments.show', $order))->assertOk();
        $secondToken = $order->payments()->first()->metadata['snap_token'];

        $this->assertSame($firstToken, $secondToken);
    }

    public function test_active_database_claim_prevents_a_second_provider_initialization(): void
    {
        $order = $this->createOrderWithItems();
        $requestFingerprint = hash('sha256', json_encode([
            'payment_method' => null,
            'amount' => (string) $order->grand_total,
        ], JSON_THROW_ON_ERROR));
        PaymentInitializationAttempt::query()->create([
            'order_id' => $order->id,
            'attempt_number' => 1,
            'idempotency_key' => (string) \Illuminate\Support\Str::uuid(),
            'request_fingerprint' => $requestFingerprint,
            'status' => 'processing',
            'lease_expires_at' => now()->addSeconds(30),
        ]);

        $gateway = \Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldNotReceive('createPayment');
        $this->app->instance(PaymentGatewayInterface::class, $gateway);

        try {
            $this->app->make(\App\Services\PaymentService::class)->initializePayment($order);
            $this->fail('An active initialization claim must block another provider call.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('sedang disiapkan', $exception->getMessage());
        }

        $this->assertSame(0, $order->payments()->count());
        $this->assertSame('processing', PaymentInitializationAttempt::query()->first()->status);
    }

    public function test_provider_timeout_retry_reuses_the_database_claim_and_midtrans_idempotency_key(): void
    {
        $order = $this->createOrderWithItems();
        $keys = [];
        $calls = 0;
        $gateway = \Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldReceive('getName')->andReturn('midtrans');
        $gateway->shouldReceive('createPayment')->twice()->andReturnUsing(function (Order $passedOrder, array $options) use (&$keys, &$calls): array {
            $keys[] = $options['idempotency_key'];
            $calls++;
            if ($calls === 1) {
                throw new \RuntimeException('simulated timeout');
            }

            return [
                'transaction_reference' => $passedOrder->order_number,
                'payment_method' => 'midtrans_snap',
                'metadata' => [
                    'snap_token' => 'retry-snap-token',
                    'token_expires_at' => now()->addMinutes(10)->toIso8601String(),
                ],
            ];
        });
        $this->app->instance(PaymentGatewayInterface::class, $gateway);
        $service = $this->app->make(\App\Services\PaymentService::class);

        try {
            $service->initializePayment($order);
            $this->fail('The first provider call should simulate a timeout.');
        } catch (\RuntimeException) {
            // A provider timeout is ambiguous, so the durable claim remains retryable.
        }

        $attempt = PaymentInitializationAttempt::query()->firstOrFail();
        $this->assertSame('retryable', $attempt->status);
        $payment = $service->initializePayment($order);

        $this->assertSame($keys[0], $keys[1]);
        $this->assertSame('completed', $attempt->fresh()->status);
        $this->assertSame($payment->id, $attempt->fresh()->payment_id);
        $this->assertSame(1, $order->payments()->count());
    }

    public function test_payment_page_is_blocked_after_the_payment_deadline(): void
{
    config(['midtrans.expiry_minutes' => 15]);

    $customer = User::factory()->customer()->create();
    $order = $this->createOrderWithItems($customer);

    $this->travel(20)->minutes();

    $this->actingAs($customer)->get(route('payments.show', $order))
        ->assertRedirect(route('orders.show', $order))
        ->assertSessionHas('error');

    $this->assertSame(0, $order->payments()->count());
}

    public function test_cancelled_order_payment_page_redirects_instead_of_failing(): void
    {
        $customer = User::factory()->customer()->create();
        $order = $this->createOrderWithItems($customer);
        $order->update(['status' => 'cancelled']);

        $this->actingAs($customer)->get(route('payments.show', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('error');
    }

    public function test_valid_webhook_signature_is_accepted(): void
    {
        $order = $this->createOrderWithItems();
        Payment::factory()->create(['order_id' => $order->id, 'amount' => 165000, 'status' => 'pending']);

        $response = $this->postJson(route('payments.webhook'), $this->signedPayload($order));

        $response->assertOk();
        $response->assertJsonPath('data.order_status', 'confirmed');
        $response->assertJsonPath('data.payment_status', 'paid');
    }

    public function test_invalid_webhook_signature_is_rejected(): void
    {
        $order = $this->createOrderWithItems();

        $payload = $this->signedPayload($order);
        $payload['signature_key'] = str_repeat('deadbeef', 8);

        $response = $this->postJson(route('payments.webhook'), $payload);

        $response->assertStatus(400);
        $order->refresh();
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('pending', $order->status);
        $this->assertSame(0, $order->payments()->count());
    }

    public function test_webhook_is_rejected_when_server_key_is_not_configured(): void
    {
        $order = $this->createOrderWithItems();

        // Penyerang yang menebak server key kosong tidak boleh lolos
        config(['midtrans.server_key' => '']);
        $payload = $this->signedPayload($order);

        $this->postJson(route('payments.webhook'), $payload)->assertStatus(400);

        $order->refresh();
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('pending', $order->status);
    }

    public function test_webhook_with_amount_mismatch_is_rejected(): void
    {
        $order = $this->createOrderWithItems();

        $payload = $this->signedPayload($order, 'settlement', grossAmount: 999000);

        $response = $this->postJson(route('payments.webhook'), $payload);

        $response->assertStatus(422);
        $order->refresh();
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('pending', $order->status);
    }

    public function test_successful_settlement_changes_payment_to_paid(): void
    {
        $order = $this->createOrderWithItems();
        $payment = Payment::factory()->create(['order_id' => $order->id, 'amount' => 165000, 'status' => 'pending']);

        $this->postJson(route('payments.webhook'), $this->signedPayload($order))->assertOk();

        $payment->refresh();
        $this->assertSame('paid', $payment->status);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_successful_settlement_confirms_pending_order(): void
    {
        $order = $this->createOrderWithItems();

        $this->postJson(route('payments.webhook'), $this->signedPayload($order))->assertOk();

        $order->refresh();
        $this->assertSame('confirmed', $order->status);
        $this->assertSame('paid', $order->payment_status);
    }

    public function test_paid_webhook_confirms_the_stock_reservation(): void
    {
        [$order, $variant] = $this->placeRealOrder(stock: 10, quantity: 2);

        $this->postJson(route('payments.webhook'), $this->signedPayload($order))->assertOk();

        // Stok tetap terpotong, reservasi berubah menjadi 'reserved' sehingga tidak ikut dilepas sweeper
        $this->assertSame(8, $variant->fresh()->stock_qty);
        $this->assertDatabaseHas('stock_reservations', [
            'product_variant_id' => $variant->id,
            'status' => StockReservation::STATUS_RESERVED,
        ]);
        $this->assertDatabaseMissing('stock_reservations', [
            'product_variant_id' => $variant->id,
            'status' => StockReservation::STATUS_PENDING,
        ]);
    }

    public function test_duplicate_webhook_is_idempotent(): void
    {
        $order = $this->createOrderWithItems();

        $this->postJson(route('payments.webhook'), $this->signedPayload($order))->assertOk();
        $order->refresh();
        $this->assertSame('confirmed', $order->status);

        // Replay the same settlement event — must not reprocess or corrupt state
        $second = $this->postJson(route('payments.webhook'), $this->signedPayload($order));
        $second->assertOk();

        $order->refresh();
        $this->assertSame('confirmed', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame(1, $order->payments()->count());
    }

    public function test_stale_pending_webhook_cannot_downgrade_a_paid_payment(): void
    {
        $order = $this->createOrderWithItems();

        $this->postJson(route('payments.webhook'), $this->signedPayload($order))->assertOk();
        $this->postJson(route('payments.webhook'), $this->signedPayload($order, 'pending'))->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->status);
        $this->assertSame('paid', $order->payments()->first()->status);
    }

    public function test_failed_payment_is_handled_correctly(): void
    {
        $order = $this->createOrderWithItems();
        $payment = Payment::factory()->create(['order_id' => $order->id, 'amount' => 165000, 'status' => 'pending']);

        $this->postJson(route('payments.webhook'), $this->signedPayload($order, 'deny'))
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'failed');

        $order->refresh();
        $payment->refresh();
        $this->assertSame('failed', $payment->status);
        $this->assertSame('failed', $order->payment_status);

        // A failed attempt remains retryable; initialization refreshes the existing history row.
        $retried = $this->app->make(\App\Services\PaymentService::class)->initializePayment($order->fresh());
        $this->assertSame(1, $order->payments()->count());
        $this->assertSame('pending', $retried->fresh()->status);
        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertNotEmpty($retried->fresh()->metadata['snap_token'] ?? null);
        // Percobaan bayar gagal tidak mengonfirmasi maupun membatalkan order: customer masih bisa mencoba lagi
        $this->assertSame('pending', $order->status);
        $this->assertNull($payment->paid_at);
    }

    public function test_challenge_capture_keeps_payment_pending(): void
    {
        $order = $this->createOrderWithItems();

        $payload = $this->signedPayload($order, 'capture');
        $payload['fraud_status'] = 'challenge';

        $this->postJson(route('payments.webhook'), $payload)
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'pending');

        $order->refresh();
        $this->assertSame('pending', $order->status);
        $this->assertSame('pending', $order->payment_status);
    }

    public function test_expired_payment_is_handled_correctly(): void
    {
        $order = $this->createOrderWithItems();
        $payment = Payment::factory()->create(['order_id' => $order->id, 'amount' => 165000, 'status' => 'pending']);

        $this->postJson(route('payments.webhook'), $this->signedPayload($order, 'expire'))
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'expired');

        $order->refresh();
        $payment->refresh();
        $this->assertSame('expired', $payment->status);
        $this->assertSame('expired', $order->payment_status);
        // Pembayaran kedaluwarsa membatalkan order dan melepas stoknya
        $this->assertSame('cancelled', $order->status);
    }

    public function test_expire_webhook_cancels_order_and_restores_reserved_stock_only_once(): void
    {
        [$order, $variant] = $this->placeRealOrder(stock: 10, quantity: 3);
        $this->assertSame(7, $variant->fresh()->stock_qty);

        $this->postJson(route('payments.webhook'), $this->signedPayload($order, 'expire'))->assertOk();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(10, $variant->fresh()->stock_qty);
        $this->assertDatabaseHas('stock_reservations', [
            'product_variant_id' => $variant->id,
            'status' => StockReservation::STATUS_RELEASED,
        ]);

        // Notifikasi ganda tidak boleh mengembalikan stok dua kali
        $this->postJson(route('payments.webhook'), $this->signedPayload($order, 'expire'))->assertOk();
        $this->assertSame(10, $variant->fresh()->stock_qty);
    }

    public function test_late_payment_on_cancelled_order_is_flagged_for_refund(): void
    {
        [$order, $variant] = $this->placeRealOrder(stock: 10, quantity: 2);

        $this->postJson(route('payments.webhook'), $this->signedPayload($order, 'expire'))->assertOk();
        $this->assertSame(10, $variant->fresh()->stock_qty);

        $this->postJson(route('payments.webhook'), $this->signedPayload($order, 'settlement'))->assertOk();

        $order->refresh();
        $payment = $order->payments()->latest('id')->first();

        // Pesanan tidak dihidupkan lagi dan stok tidak dialokasikan ulang, tapi uang yang masuk tercatat
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('paid', $payment->status);
        $this->assertTrue($payment->metadata['needs_refund'] ?? false);
        $this->assertSame(10, $variant->fresh()->stock_qty);
    }

    public function test_refunded_payment_is_handled_correctly(): void
    {
        $order = $this->createOrderWithItems();
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 165000,
            'status' => 'paid',
            'paid_at' => now()->subHour(),
        ]);
        $order->update(['payment_status' => 'paid', 'status' => 'confirmed']);

        $this->postJson(route('payments.webhook'), $this->signedPayload($order, 'refund'))
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'refunded');

        $order->refresh();
        $payment->refresh();
        $this->assertSame('refunded', $payment->status);
        $this->assertSame('refunded', $order->payment_status);
        // Refund must not advance the fulfillment state machine
        $this->assertSame('confirmed', $order->status);
    }

    public function test_guest_is_redirected_from_payment_page(): void
    {
        $order = $this->createOrderWithItems();

        // No authenticated user => redirected to login (bootstrap/app.php guest redirect)
        $this->get(route('payments.show', $order))->assertRedirect(route('login'));
    }

    public function test_payment_status_endpoint_returns_state_for_owner_only(): void
    {
        $owner = User::factory()->customer()->create();
        $intruder = User::factory()->customer()->create();
        $order = $this->createOrderWithItems($owner);

        $this->actingAs($owner)->getJson(route('payments.status', $order))
            ->assertOk()
            ->assertJson(['order_status' => 'pending', 'payment_status' => 'pending']);

        $this->actingAs($intruder)->getJson(route('payments.status', $order))->assertForbidden();
    }

    /**
     * Ensure the local payment simulation route is intentionally removed.
     */
    public function test_simulate_route_is_not_defined(): void
    {
        $this->assertFalse(Route::has('payments.simulate'));
    }
}
