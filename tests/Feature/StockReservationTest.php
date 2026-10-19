<?php

namespace Tests\Feature;

use App\Models\ProductVariant;
use App\Models\StockReservation;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\StockReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StockReservationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to create a customer user with an address.
     */
    protected function createCustomer(): User
    {
        $user = User::factory()->create();
        $user->addresses()->create([
            'recipient_name' => 'John Doe',
            'phone' => '08123456789',
            'address_line' => 'Jl. Example 123',
            'city' => 'Jakarta',
            'province' => 'Jakarta',
            'postal_code' => '12345',
            'is_default' => true,
        ]);

        return $user;
    }

    /**
     * Test successful reservation and stock deduction for regular cart flow.
     */
    public function test_regular_cart_reservation_and_deduction(): void
    {
        $variant = ProductVariant::factory()->create(['stock_qty' => 10]);

        $user = $this->createCustomer();
        $cartService = $this->app->make(CartService::class);
        $cart = $cartService->getOrCreateCart($user, session()->getId());
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 3,
            'unit_price' => $variant->finalPrice(),
        ]);

        $checkout = $this->app->make(CheckoutService::class);
        $order = $checkout->placeOrder($user, [
            'address_id' => $user->addresses()->first()->id,
            'shipping_method' => 'regular',
        ]);

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
        $this->assertDatabaseHas('stock_reservations', [
            'product_variant_id' => $variant->id,
            'quantity' => 3,
            'status' => StockReservation::STATUS_PENDING,
        ]);
        $variant->refresh();
        $this->assertEquals(7, $variant->stock_qty);
    }

    /**
     * Test that buy‑now flow does not affect regular cart and clears session flag.
     */
    public function test_buy_now_isolation_and_session_cleanup(): void
    {
        $variant = ProductVariant::factory()->create(['stock_qty' => 5]);
        $user = $this->createCustomer();
        session(['buy_now' => ['product_variant_id' => $variant->id, 'quantity' => 2]]);

        $checkout = $this->app->make(CheckoutService::class);
        $order = $checkout->placeOrder($user, [
            'address_id' => $user->addresses()->first()->id,
            'shipping_method' => 'regular',
        ]);

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
        $this->assertDatabaseHas('stock_reservations', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $this->assertFalse(session()->has('buy_now'), 'buy_now session should be cleared');
        $cart = $this->app->make(CartService::class)->getOrCreateCart($user, session()->getId());
        $this->assertTrue($cart->items->isEmpty(), 'Regular cart should remain empty after buy‑now');
    }

    /**
     * Test that expires_at is set approximately 15 minutes from now.
     */
    
public function test_reservation_expires_at_is_set_correctly(): void
{
    Carbon::setTestNow(Carbon::now());

    // Masa berlaku khusus untuk test ini: 24 jam
    config(['midtrans.expiry_minutes' => 1440]);

    $variant = ProductVariant::factory()->create(['stock_qty' => 5]);
    $user = $this->createCustomer();

    $cartService = $this->app->make(CartService::class);
    $cart = $cartService->getOrCreateCart($user, session()->getId());

    $cart->items()->create([
        'product_variant_id' => $variant->id,
        'quantity' => 2,
        'unit_price' => $variant->finalPrice(),
    ]);

    $checkout = $this->app->make(CheckoutService::class);
    $order = $checkout->placeOrder($user, [
        'address_id' => $user->addresses()->first()->id,
        'shipping_method' => 'regular',
    ]);

    // Ambil reservasi yang dibuat untuk item pesanan ini
    $reservation = StockReservation::whereIn(
        'order_item_id',
        $order->items()->select('id')
    )->first();

    $this->assertNotNull($reservation);

    $expected = Carbon::now()->addMinutes(1440);
    $diff = abs($expected->diffInSeconds($reservation->expires_at));

    $this->assertLessThanOrEqual(
        60,
        $diff,
        'Expires_at should be within 1 minute of 24 hours'
    );
}


    /**
     * Test that insufficient stock rolls back the entire reservation and does not change stock.
     */
    public function test_insufficient_stock_rollback(): void
    {
        $variant = ProductVariant::factory()->create(['stock_qty' => 2]);
        $user = $this->createCustomer();

        $cartService = $this->app->make(CartService::class);
        $cart = $cartService->getOrCreateCart($user, session()->getId());
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 3,
            'unit_price' => $variant->finalPrice(),
        ]);

        $this->expectException(ValidationException::class);

        $checkout = $this->app->make(CheckoutService::class);
        $checkout->placeOrder($user, [
            'address_id' => $user->addresses()->first()->id,
            'shipping_method' => 'regular',
        ]);
    }

    /**
     * Test that releasing a reservation is idempotent.
     */
    public function test_release_reservation_idempotent(): void
    {
        $variant = ProductVariant::factory()->create(['stock_qty' => 5]);
        $reservation = StockReservation::create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'status' => StockReservation::STATUS_PENDING,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        // Simulate stock deduction that would have happened during reservation
        $variant->decrement('stock_qty', $reservation->quantity);

        $service = $this->app->make(StockReservationService::class);
        $service->releaseReservation($reservation);
        $service->releaseReservation($reservation); // second call should have no effect

        $variant->refresh();
        $this->assertEquals(5, $variant->stock_qty, 'Stock should be returned only once');
        $reservation->refresh();
        $this->assertEquals(StockReservation::STATUS_RELEASED, $reservation->status);
    }

    /**
     * Test that releasing a non‑pending reservation does nothing.
     */
    public function test_release_non_pending_reservation_no_stock_change(): void
    {
        $variant = ProductVariant::factory()->create(['stock_qty' => 5]);
        $reservation = StockReservation::create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'status' => StockReservation::STATUS_RELEASED,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        $service = $this->app->make(StockReservationService::class);
        $service->releaseReservation($reservation);

        $variant->refresh();
        $this->assertEquals(5, $variant->stock_qty, 'Stock should remain unchanged');
    }
}
