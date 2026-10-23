<?php

namespace Tests\Feature;

use App\Mail\ReviewReminderMail;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tests\Concerns\InteractsWithShippingQuotes;

class OrderTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithShippingQuotes;

    protected function createVariant(string $productName = 'Pashmina Silk', string $variantName = 'Mocca', int $stock = 10, float $basePrice = 75000): ProductVariant
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => $productName,
            'base_price' => $basePrice,
            'is_active' => true,
        ]);

        return ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => $variantName,
            'sku' => 'SKU-'.strtoupper(fake()->lexify('????')).'-01',
            'additional_price' => 0,
            'stock_qty' => $stock,
            'is_active' => true,
        ]);
    }

    public function test_successful_checkout_creates_order_and_items(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant(productName: 'Bella Square Voal', variantName: 'Dusty Pink', stock: 10, basePrice: 45000);

        $cart = Cart::create(['user_id' => $customer->id]);
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 45000,
        ]);

        $address = Address::factory()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Aisyah Putri',
            'phone' => '081234567890',
            'address_line' => 'Jl. Anggrek No. 20',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40115',
        ]);

        $response = $this->actingAs($customer)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => $this->fakeShippingQuote(weight: (int) $variant->weight_gram * 2),
            'customer_note' => 'Mohon dipacking rapi untuk hadiah.',
        ]);

        $order = Order::where('user_id', $customer->id)->first();
        $this->assertNotNull($order);

        $response->assertRedirect(route('payments.show', $order));

        // Subtotal = 2 * 45000 = 90000. Quote shipping = 16000. Grand total = 106000
        $this->assertEquals(90000.0, (float) $order->subtotal);
        $this->assertEquals(16000.0, (float) $order->shipping_cost);
        $this->assertEquals(106000.0, (float) $order->grand_total);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals('Aisyah Putri', $order->shipping_recipient_name);
        $this->assertEquals('Mohon dipacking rapi untuk hadiah.', $order->customer_note);

        // Cart should be cleared
        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $cart->id,
        ]);
    }

    public function test_order_items_contain_immutable_snapshots(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant(productName: 'Bergo Maryam Diamond', variantName: 'Sage Green', stock: 8, basePrice: 50000);

        $cart = Cart::create(['user_id' => $customer->id]);
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 50000,
        ]);

        $address = Address::factory()->create(['user_id' => $customer->id]);

        $this->actingAs($customer)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => $this->fakeShippingQuote(weight: (int) $variant->weight_gram * 2),
        ]);

        $order = Order::where('user_id', $customer->id)->first();
        $orderItem = $order->items()->first();

        $this->assertNotNull($orderItem);
        $this->assertEquals('Bergo Maryam Diamond', $orderItem->product_name);
        $this->assertEquals('Sage Green', $orderItem->variant_name);
        $this->assertEquals($variant->sku, $orderItem->sku);
        $this->assertEquals(50000.0, (float) $orderItem->unit_price);
        $this->assertEquals(2, $orderItem->quantity);
        $this->assertEquals(100000.0, (float) $orderItem->subtotal);

        // If product/variant name or price changes later, order_item snapshot remains unchanged
        $variant->product->update(['name' => 'Nama Produk Berubah']);
        $variant->update(['name' => 'Warna Berubah', 'sku' => 'SKU-BARU']);

        $refreshedItem = $orderItem->fresh();
        $this->assertEquals('Bergo Maryam Diamond', $refreshedItem->product_name);
        $this->assertEquals('Sage Green', $refreshedItem->variant_name);
    }

    public function test_stock_is_deducted_and_inventory_movement_created(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant(stock: 10);

        $cart = Cart::create(['user_id' => $customer->id]);
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 3,
            'unit_price' => 75000,
        ]);

        $address = Address::factory()->create(['user_id' => $customer->id]);

        $this->actingAs($customer)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => $this->fakeShippingQuote(weight: (int) $variant->weight_gram * 3),
        ]);

        // Stock must be reduced from 10 to 7
        $this->assertEquals(7, $variant->fresh()->stock_qty);

        // Inventory movement created
        $this->assertDatabaseHas('inventory_movements', [
            'product_variant_id' => $variant->id,
            'type' => 'out',
            'quantity' => 3,
            'created_by' => $customer->id,
        ]);
    }

    public function test_order_number_is_unique(): void
    {
        $customer = User::factory()->customer()->create();
        $address = Address::factory()->create(['user_id' => $customer->id]);

        $variant1 = $this->createVariant(stock: 5);
        $cart = Cart::create(['user_id' => $customer->id]);
        $cart->items()->create([
            'product_variant_id' => $variant1->id,
            'quantity' => 1,
            'unit_price' => 75000,
        ]);

        $this->actingAs($customer)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => $this->fakeShippingQuote(),
        ]);

        $order1 = Order::latest('id')->first();

        // Second checkout
        $variant2 = $this->createVariant(stock: 5);
        $cart->items()->create([
            'product_variant_id' => $variant2->id,
            'quantity' => 1,
            'unit_price' => 75000,
        ]);

        $this->actingAs($customer)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => $this->fakeShippingQuote(),
        ]);

        $order2 = Order::latest('id')->first();

        $this->assertNotEquals($order1->order_number, $order2->order_number);
        $this->assertStringStartsWith('ORD-', $order1->order_number);
        $this->assertStringStartsWith('ORD-', $order2->order_number);
    }

    public function test_customer_can_view_own_orders_and_order_detail(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'processing',
        ]);

        // Order history
        $indexResponse = $this->actingAs($customer)->get(route('orders.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee($order->order_number);
        $indexResponse->assertSee('Diproses');

        // Order detail
        $showResponse = $this->actingAs($customer)->get(route('orders.show', $order));
        $showResponse->assertOk();
        $showResponse->assertSee($order->order_number);
        $showResponse->assertSee('Lacak Perjalanan Pesanan');
        $showResponse->assertSee('Diproses');
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();

        $order1 = Order::factory()->create(['user_id' => $customer1->id]);

        $response = $this->actingAs($customer2)->get(route('orders.show', $order1));
        $response->assertForbidden();
    }

    public function test_failed_transaction_does_not_create_partial_order(): void
    {
        $customer = User::factory()->customer()->create();
        $variant1 = $this->createVariant(stock: 5, basePrice: 50000);
        $variant2 = $this->createVariant(stock: 1, basePrice: 50000);

        $cart = Cart::create(['user_id' => $customer->id]);
        $cart->items()->create([
            'product_variant_id' => $variant1->id,
            'quantity' => 2,
            'unit_price' => 50000,
        ]);
        // Variant 2 only has 1 in stock, but cart requests 3
        $cart->items()->create([
            'product_variant_id' => $variant2->id,
            'quantity' => 3,
            'unit_price' => 50000,
        ]);

        $address = Address::factory()->create(['user_id' => $customer->id]);

        $response = $this->actingAs($customer)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => $this->fakeShippingQuote(weight: (int) $variant1->weight_gram * 2 + (int) $variant2->weight_gram * 3),
        ]);

        $response->assertSessionHasErrors('cart');

        // No orders created
        $this->assertEquals(0, Order::count());
        $this->assertEquals(0, OrderItem::count());

        // Stock was NOT partially deducted
        $this->assertEquals(5, $variant1->fresh()->stock_qty);
        $this->assertEquals(1, $variant2->fresh()->stock_qty);
        $this->assertEquals(0, InventoryMovement::count());
    }

    public function test_order_tracking_status_is_displayed_correctly(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'shipped',
        ]);

        $response = $this->actingAs($customer)->get(route('orders.show', $order));
        $response->assertOk();

        // Must display all status labels in tracking timeline
        $response->assertSee('Pesanan Dibuat');
        $response->assertSee('Terkonfirmasi');
        $response->assertSee('Diproses');
        $response->assertSee('Dikemas');
        $response->assertSee('Dikirim');
        $response->assertSee('Tiba di Tujuan');
        $response->assertSee('Selesai');
    }

    public function test_admin_can_update_shipment_details_and_tracking_number(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create();
        $shipment = $order->shipment()->create([
            'courier' => 'JNE',
            'service' => 'REG',
            'tracking_number' => null,
            'shipping_cost' => 15000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.orders.shipment', $order), [
            'courier' => 'JNE',
            'service' => 'REG',
            'tracking_number' => 'JKKA2131928213',
            'shipping_cost' => 15000,
            'status' => 'shipped',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $shipment->refresh();
        $this->assertEquals('JKKA2131928213', $shipment->tracking_number);
        $this->assertEquals('shipped', $shipment->status);
        $this->assertNotNull($shipment->shipped_at);
    }
}
