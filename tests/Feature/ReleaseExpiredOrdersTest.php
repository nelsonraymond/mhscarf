<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\StockReservation;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Concerns\InteractsWithShippingQuotes;

class ReleaseExpiredOrdersTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithShippingQuotes;

    /**
     * @return array{0: Order, 1: ProductVariant}
     */
    protected function placeOrder(int $stock = 10, int $quantity = 2): array
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

        return [$order, $variant];
    }

    protected function mockGateway(callable $configure): void
    {
        $this->mock(PaymentGatewayInterface::class, function (MockInterface $mock) use ($configure) {
            $mock->shouldReceive('getName')->andReturn('midtrans');
            $configure($mock);
        });
    }

    public function test_overdue_unpaid_order_is_cancelled_and_stock_is_restored(): void
    {
        config([
    'midtrans.expiry_minutes' => 15,
    'midtrans.expiry_grace_minutes' => 3,
]);
        [$order, $variant] = $this->placeOrder(stock: 10, quantity: 2);
        $this->assertSame(8, $variant->fresh()->stock_qty);

        $this->mockGateway(fn (MockInterface $m) => $m->shouldReceive('fetchStatus')
            ->once()
            ->with($order->order_number)
            ->andReturn(null));

        $this->travel(25)->minutes();
        $this->artisan('orders:release-expired')->assertSuccessful();

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('expired', $order->payment_status);
        $this->assertSame(10, $variant->fresh()->stock_qty);
        $this->assertDatabaseHas('stock_reservations', [
            'product_variant_id' => $variant->id,
            'status' => StockReservation::STATUS_RELEASED,
        ]);
    }

    public function test_running_the_command_twice_restores_stock_only_once(): void
    {
        config([
    'midtrans.expiry_minutes' => 15,
    'midtrans.expiry_grace_minutes' => 3,
]);
        [$order, $variant] = $this->placeOrder(stock: 10, quantity: 2);

        $this->mockGateway(fn (MockInterface $m) => $m->shouldReceive('fetchStatus')->andReturn(null));

        $this->travel(25)->minutes();
        $this->artisan('orders:release-expired')->assertSuccessful();
        $this->artisan('orders:release-expired')->assertSuccessful();

        $this->assertSame(10, $variant->fresh()->stock_qty);
    }

    public function test_order_inside_the_grace_period_is_left_alone(): void
    {
        config([
    'midtrans.expiry_minutes' => 15,
    'midtrans.expiry_grace_minutes' => 3,
]);
        [$order, $variant] = $this->placeOrder(stock: 10, quantity: 2);

        $this->mockGateway(fn (MockInterface $m) => $m->shouldNotReceive('fetchStatus'));

        // 16 menit: reservasi (15) sudah lewat, tapi masih dalam toleransi 3 menit
        $this->travel(16)->minutes();
        $this->artisan('orders:release-expired')->assertSuccessful();

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(8, $variant->fresh()->stock_qty);
    }

    public function test_order_paid_at_the_gateway_is_confirmed_instead_of_expired(): void
    {
        config([
    'midtrans.expiry_minutes' => 15,
    'midtrans.expiry_grace_minutes' => 3,
]);
        [$order, $variant] = $this->placeOrder(stock: 10, quantity: 2);

        $this->mockGateway(fn (MockInterface $m) => $m->shouldReceive('fetchStatus')->once()->andReturn([
            'order_number' => $order->order_number,
            'transaction_reference' => 'TRX-SWEEP-1',
            'status' => 'paid',
            'payment_method' => 'bank_transfer',
            'amount' => (float) $order->grand_total,
            'paid_at' => now(),
            'raw' => [],
        ]));

        $this->travel(25)->minutes();
        $this->artisan('orders:release-expired')->assertSuccessful();

        $order->refresh();
        $this->assertSame('confirmed', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame(8, $variant->fresh()->stock_qty);
        $this->assertDatabaseHas('stock_reservations', [
            'product_variant_id' => $variant->id,
            'status' => StockReservation::STATUS_RESERVED,
        ]);
    }

    public function test_order_still_pending_at_the_gateway_is_skipped(): void
    {
        config([
    'midtrans.expiry_minutes' => 15,
    'midtrans.expiry_grace_minutes' => 3,
]);
        [$order, $variant] = $this->placeOrder(stock: 10, quantity: 2);

        $this->mockGateway(fn (MockInterface $m) => $m->shouldReceive('fetchStatus')->once()->andReturn([
            'order_number' => $order->order_number,
            'transaction_reference' => 'TRX-SWEEP-2',
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
            'amount' => (float) $order->grand_total,
            'paid_at' => null,
            'raw' => [],
        ]));

        $this->travel(25)->minutes();
        $this->artisan('orders:release-expired')->assertSuccessful();

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(8, $variant->fresh()->stock_qty);
    }

    public function test_gateway_error_does_not_expire_the_order(): void
    {
        config([
    'midtrans.expiry_minutes' => 15,
    'midtrans.expiry_grace_minutes' => 3,
]);
        [$order, $variant] = $this->placeOrder(stock: 10, quantity: 2);

        $this->mockGateway(fn (MockInterface $m) => $m->shouldReceive('fetchStatus')
            ->once()
            ->andThrow(new \RuntimeException('Midtrans tidak dapat dihubungi')));

        $this->travel(25)->minutes();
        $this->artisan('orders:release-expired')->assertSuccessful();

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(8, $variant->fresh()->stock_qty);
    }
}
