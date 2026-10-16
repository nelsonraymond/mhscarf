<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    private function createVariant(string $variantName = 'Mocca', int $stock = 10, float $basePrice = 75000): ProductVariant
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => "Khimar Syar'i",
            'base_price' => $basePrice,
            'is_active' => true,
        ]);

        return ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => $variantName,
            'sku' => 'SKU-'.strtoupper(fake()->lexify('????')).'-CXL',
            'additional_price' => 0,
            'stock_qty' => $stock,
            'is_active' => true,
        ]);
    }

    /**
     * Create an order in the given status with one item linked to a real variant.
     */
    private function createOrder(User $customer, string $status, ProductVariant $variant, int $quantity = 2): Order
    {
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => $status,
        ]);

        $unitPrice = 75000;

        $order->items()->create([
            'product_variant_id' => $variant->id,
            'product_id' => $variant->product_id,
            'product_name' => $variant->product->name,
            'variant_name' => $variant->name,
            'sku' => $variant->sku,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $unitPrice * $quantity,
        ]);

        return $order->fresh();
    }

    public function test_customer_sees_cancel_button_for_pending_order(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant();
        $order = $this->createOrder($customer, 'pending', $variant);

        $response = $this->actingAs($customer)
            ->get(route('orders.show', $order));

        $response->assertOk()
            ->assertSee('Batalkan Pesanan')
            ->assertSee('data-confirm-title="Batalkan pesanan ini?"', false)
            ->assertSee('Pembatalan hanya tersedia sebelum pesanan dikirim.');
    }

    public function test_customer_sees_cancel_button_for_confirmed_order(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant();
        $order = $this->createOrder($customer, 'confirmed', $variant);

        $response = $this->actingAs($customer)
            ->get(route('orders.show', $order));

        $response->assertOk()
            ->assertSee('Batalkan Pesanan');
    }

    public function test_customer_cannot_see_cancel_button_for_shipped_order(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant();
        $order = $this->createOrder($customer, 'shipped', $variant);

        $response = $this->actingAs($customer)
            ->get(route('orders.show', $order));

        $response->assertOk()
            ->assertDontSee('Batalkan Pesanan');
    }

    public function test_customer_cannot_see_cancel_button_for_completed_order(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant();
        $order = $this->createOrder($customer, 'completed', $variant);

        $response = $this->actingAs($customer)
            ->get(route('orders.show', $order));

        $response->assertOk()
            ->assertDontSee('Batalkan Pesanan');
    }

    public function test_customer_can_cancel_own_pending_order_through_http(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant(stock: 10);
        $order = $this->createOrder($customer, 'pending', $variant, quantity: 2);

        // Simulate stock already deducted at checkout time.
        $variant->update(['stock_qty' => 8]);

        $response = $this->actingAs($customer)
            ->post(route('orders.cancel', $order));

        $response->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(10, (int) $variant->fresh()->stock_qty);
        $this->assertSame(
            1,
            InventoryMovement::where('reference_type', Order::class)
                ->where('reference_id', $order->id)
                ->where('type', 'return')
                ->count()
        );
    }

    public function test_another_customer_cannot_cancel_the_order(): void
    {
        $owner = User::factory()->customer()->create();
        $intruder = User::factory()->customer()->create();
        $variant = $this->createVariant(stock: 10);
        $order = $this->createOrder($owner, 'pending', $variant);

        $response = $this->actingAs($intruder)
            ->from(route('orders.index'))
            ->post(route('orders.cancel', $order));

        $response->assertForbidden();

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(10, (int) $variant->fresh()->stock_qty);
        $this->assertSame(
            0,
            InventoryMovement::where('reference_type', Order::class)
                ->where('reference_id', $order->id)
                ->where('type', 'return')
                ->count()
        );
    }
}
