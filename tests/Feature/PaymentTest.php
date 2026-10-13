<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

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
        $serverKey = (string) config('services.midtrans.server_key', '');

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
        // Failed payment must NOT confirm the order
        $this->assertSame('pending', $order->status);
        $this->assertNull($payment->paid_at);
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
        $this->assertSame('pending', $order->status);
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

    /**
     * Ensure the local payment simulation route is intentionally removed.
     */
    public function test_simulate_route_is_not_defined(): void
    {
        $this->assertFalse(Route::has('payments.simulate'));
    }
}
