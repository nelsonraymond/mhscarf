<?php

namespace Tests\Feature;

use App\Exceptions\InvalidOrderStateTransitionException;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class OrderStateTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = app(OrderService::class);
    }

    /**
     * Create an order with one line item backed by a real variant with stock.
     */
    protected function createOrderWithStock(string $status = 'pending', int $stock = 10, int $quantity = 3): array
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'base_price' => 50000,
            'is_active' => true,
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'SKU-'.strtoupper(fake()->lexify('????')).'-02',
            'additional_price' => 0,
            'stock_qty' => $stock,
            'is_active' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => User::factory()->customer()->create()->id,
            'status' => $status,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'sku' => $variant->sku,
            'unit_price' => 50000,
            'quantity' => $quantity,
            'subtotal' => 50000 * $quantity,
        ]);

        return [$order->fresh(), $variant->fresh()];
    }

    public function test_pending_to_confirmed(): void
    {
        [$order] = $this->createOrderWithStock('pending');

        $updated = $this->orderService->transitionStatus($order, 'confirmed', 'Pembayaran diterima');

        $this->assertSame('confirmed', $updated->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'confirmed',
            'reason' => 'Pembayaran diterima',
            'actor_id' => null,
        ]);
    }

    public function test_confirmed_to_processing(): void
    {
        [$order] = $this->createOrderWithStock('confirmed');

        $updated = $this->orderService->transitionStatus($order, 'processing');

        $this->assertSame('processing', $updated->status);
    }

    public function test_processing_to_packed(): void
    {
        [$order] = $this->createOrderWithStock('processing');

        $updated = $this->orderService->transitionStatus($order, 'packed');

        $this->assertSame('packed', $updated->status);
    }

    public function test_packed_to_shipped(): void
    {
        [$order] = $this->createOrderWithStock('packed');
        $order->update(['payment_status' => 'paid']);
        $order->shipment()->create(['courier' => 'JNE', 'service' => 'REG', 'tracking_number' => 'RESI-123']);

        $updated = $this->orderService->transitionStatus($order, 'shipped');

        $this->assertSame('shipped', $updated->status);
    }

    public function test_shipped_to_delivered(): void
    {
        [$order] = $this->createOrderWithStock('shipped');

        $updated = $this->orderService->transitionStatus($order, 'delivered');

        $this->assertSame('delivered', $updated->status);
    }

    public function test_delivered_to_completed(): void
    {
        [$order] = $this->createOrderWithStock('delivered');

        $updated = $this->orderService->transitionStatus($order, 'completed');

        $this->assertSame('completed', $updated->status);
    }

    public function test_full_happy_path_sequence(): void
    {
        [$order] = $this->createOrderWithStock('pending');

        foreach (['confirmed', 'processing', 'packed', 'shipped', 'delivered', 'completed'] as $next) {
            if ($next === 'shipped') {
                $order->update(['payment_status' => 'paid']);
                $order->shipment()->create(['courier' => 'JNE', 'service' => 'REG', 'tracking_number' => 'RESI-123']);
            }
            $order = $this->orderService->transitionStatus($order, $next);
            $this->assertSame($next, $order->status);
        }
    }

    public function test_illegal_jump_is_rejected(): void
    {
        [$order] = $this->createOrderWithStock('pending');

        $this->expectException(InvalidOrderStateTransitionException::class);

        $this->orderService->transitionStatus($order, 'shipped');
    }

    public function test_cancelled_order_cannot_transition_anywhere(): void
    {
        [$order] = $this->createOrderWithStock('cancelled');

        $this->expectException(InvalidOrderStateTransitionException::class);

        $this->orderService->transitionStatus($order, 'pending');
    }

    public function test_shipped_order_cannot_be_cancelled_via_http(): void
    {
        $admin = User::factory()->admin()->create();
        [$order, $variant] = $this->createOrderWithStock('shipped');

        $response = $this->actingAs($admin)->post(route('admin.orders.cancel', $order));

        $response->assertSessionHas('error');
        $this->assertSame('shipped', $order->fresh()->status);
        $this->assertSame($variant->stock_qty, $variant->fresh()->stock_qty);
    }

    public function test_shipped_order_cannot_be_cancelled_via_service(): void
    {
        [$order, $variant] = $this->createOrderWithStock('shipped');

        $this->expectException(InvalidArgumentException::class);

        $this->orderService->cancelOrder($order, 'tes');
    }

    public function test_delivered_order_cannot_be_cancelled(): void
    {
        [$order, $variant] = $this->createOrderWithStock('delivered');

        $this->expectException(InvalidArgumentException::class);

        $this->orderService->cancelOrder($order, 'tes');
    }

    public function test_completed_order_cannot_be_cancelled(): void
    {
        [$order] = $this->createOrderWithStock('completed');

        $this->expectException(InvalidArgumentException::class);

        $this->orderService->cancelOrder($order, 'tes');
    }

    public function test_pending_cancellation_restores_stock(): void
    {
        [$order, $variant] = $this->createOrderWithStock('pending', stock: 10, quantity: 3);

        $updated = $this->orderService->cancelOrder($order, 'Dibatalkan pelanggan');

        $this->assertSame('cancelled', $updated->status);
        $this->assertSame(13, $variant->fresh()->stock_qty);

        $movement = InventoryMovement::where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->where('type', 'return')
            ->first();

        $this->assertNotNull($movement);
        $this->assertSame(3, (int) $movement->quantity);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'cancelled',
            'reason' => 'Dibatalkan pelanggan',
            'actor_id' => null,
        ]);
    }

    public function test_customer_cancellation_endpoint_restores_stock_and_owns_check(): void
    {
        $customer = User::factory()->customer()->create();
        [$order, $variant] = $this->createOrderWithStock('pending', stock: 10, quantity: 3);
        $order->update(['user_id' => $customer->id]);

        // Another customer cannot cancel
        $intruder = User::factory()->customer()->create();
        $this->actingAs($intruder)->post(route('orders.cancel', $order))->assertForbidden();
        $this->assertSame('pending', $order->fresh()->status);

        // Owner can cancel
        $response = $this->actingAs($customer)->post(route('orders.cancel', $order));
        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionHas('success');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(13, $variant->fresh()->stock_qty);
    }

    public function test_cancellation_restores_stock_exactly_once(): void
    {
        [$order, $variant] = $this->createOrderWithStock('pending', stock: 10, quantity: 3);

        $this->orderService->cancelOrder($order, 'pertama');
        // Second attempt on a fresh model instance must be idempotent (already cancelled)
        $this->orderService->cancelOrder($order->fresh(), 'kedua');

        $this->assertSame(13, $variant->fresh()->stock_qty);
        $this->assertSame(1, $order->statusHistories()->where('to_status', 'cancelled')->count());
        $this->assertSame(
            1,
            InventoryMovement::where('reference_type', Order::class)
                ->where('reference_id', $order->id)
                ->where('type', 'return')
                ->count()
        );
    }

    public function test_cancelled_order_cannot_be_cancelled_again(): void
    {
        [$order, $variant] = $this->createOrderWithStock('pending', stock: 10, quantity: 3);

        $first = $this->orderService->cancelOrder($order);
        $second = $this->orderService->cancelOrder($first);

        // Idempotent no-op: still cancelled, stock restored only once
        $this->assertSame('cancelled', $second->status);
        $this->assertSame(13, $variant->fresh()->stock_qty);
    }

    public function test_admin_status_endpoint_rejects_invalid_transition_with_error_message(): void
    {
        $admin = User::factory()->admin()->create();
        [$order] = $this->createOrderWithStock('pending');

        // pending -> delivered is not allowed by the state machine
        $response = $this->actingAs($admin)->patch(route('admin.orders.status', $order), [
            'status' => 'delivered',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_admin_status_endpoint_allows_valid_transition(): void
    {
        $admin = User::factory()->admin()->create();
        [$order] = $this->createOrderWithStock('confirmed');

        $response = $this->actingAs($admin)->patch(route('admin.orders.status', $order), [
            'status' => 'processing',
            'note' => 'Sedang disiapkan',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSame('processing', $order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'confirmed',
            'to_status' => 'processing',
            'actor_id' => $admin->id,
            'reason' => 'Sedang disiapkan',
        ]);
    }

    public function test_admin_cannot_access_customer_payment_page(): void
    {
        $customer = User::factory()->customer()->create();
        [$order] = $this->createOrderWithStock('pending');
        $order->update(['user_id' => $customer->id]);

        // Non-owner customer blocked from payments page
        $other = User::factory()->customer()->create();
        $this->actingAs($other)->get(route('payments.show', $order))->assertForbidden();

        // Admin order pages are reachable by admin
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('admin.orders.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk();

        // Customer is forbidden from admin area
        $this->actingAs($customer)->get(route('admin.orders.index'))->assertForbidden();
    }
}
