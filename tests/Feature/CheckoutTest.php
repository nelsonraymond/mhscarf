<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function createVariant(int $stock = 10, float $basePrice = 50000, float $addPrice = 10000): ProductVariant
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'base_price' => $basePrice,
            'is_active' => true,
        ]);

        return ProductVariant::factory()->create([
            'product_id' => $product->id,
            'additional_price' => $addPrice,
            'stock_qty' => $stock,
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_checkout(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('login'));

        $postResponse = $this->post(route('checkout.store'), []);
        $postResponse->assertRedirect(route('login'));
    }

    public function test_customer_with_empty_cart_cannot_access_checkout(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('checkout.index'));
        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('warning');
    }

    public function test_checkout_page_loads_with_cart_and_addresses(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant(stock: 10, basePrice: 60000, addPrice: 5000);

        $cart = Cart::create(['user_id' => $customer->id]);
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 65000,
        ]);

        $address = Address::factory()->create([
            'user_id' => $customer->id,
            'is_default' => true,
        ]);

        $response = $this->actingAs($customer)->get(route('checkout.index'));

        $response->assertOk();
        $response->assertViewHas('subtotal', 130000.0);
        $response->assertViewHas('shippingCost', 15000.0);
        $response->assertViewHas('grandTotal', 145000.0);
        $response->assertSee($address->recipient_name);
        $response->assertSee('Reguler');
        $response->assertSee('Express');
    }

    public function test_checkout_requires_shipping_address(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant(stock: 5);

        $cart = Cart::create(['user_id' => $customer->id]);
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 60000,
        ]);

        $response = $this->actingAs($customer)->post(route('checkout.store'), [
            'shipping_method' => 'regular',
        ]);

        $response->assertSessionHasErrors('address_id');
    }

    public function test_checkout_requires_address_owned_by_customer(): void
    {
        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();

        $addressOtherUser = Address::factory()->create(['user_id' => $customer2->id]);
        $variant = $this->createVariant(stock: 5);

        $cart = Cart::create(['user_id' => $customer1->id]);
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 60000,
        ]);

        $response = $this->actingAs($customer1)->post(route('checkout.store'), [
            'address_id' => $addressOtherUser->id,
            'shipping_method' => 'regular',
        ]);

        $response->assertSessionHasErrors('address_id');
    }

    public function test_checkout_validates_stock_availability(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant(stock: 2);

        $cart = Cart::create(['user_id' => $customer->id]);
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 5, // Exceeds available stock of 2
            'unit_price' => 60000,
        ]);

        $address = Address::factory()->create(['user_id' => $customer->id]);

        $response = $this->actingAs($customer)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_method' => 'regular',
        ]);

        $response->assertSessionHasErrors('cart');
    }

        public function test_duplicate_checkout_token_returns_same_order(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant(stock: 10);
        $address = Address::factory()->create(['user_id' => $customer->id, 'is_default' => true]);

        $cart = Cart::create(['user_id' => $customer->id]);
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 60000,
        ]);

        $payload = [
            'address_id' => $address->id,
            'shipping_method' => 'regular',
            'checkout_token' => 'token-test-123',
        ];

        $first = $this->actingAs($customer)->postJson(route('checkout.store'), $payload)->assertOk();
        $second = $this->actingAs($customer)->postJson(route('checkout.store'), $payload)->assertOk();

        // Token yang sama mengembalikan pesanan yang sama, bukan membuat pesanan baru
        $this->assertSame($first->json('order_id'), $second->json('order_id'));
        $this->assertSame(1, Order::where('user_id', $customer->id)->count());

        // Stok hanya terpotong sekali
        $this->assertEquals(9, $variant->fresh()->stock_qty);
    }

    public function test_failed_checkout_releases_token_so_user_can_retry(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant(stock: 2);
        $address = Address::factory()->create(['user_id' => $customer->id, 'is_default' => true]);

        $cart = Cart::create(['user_id' => $customer->id]);
        $item = $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 5, // melebihi stok 2
            'unit_price' => 60000,
        ]);

        $payload = [
            'address_id' => $address->id,
            'shipping_method' => 'regular',
            'checkout_token' => 'token-retry-456',
        ];

        $this->actingAs($customer)->postJson(route('checkout.store'), $payload)->assertStatus(422);

        // Pesanan gagal dibuat, jadi token harus dilepas
        $this->assertFalse(Cache::has('checkout-idem:'.$customer->id.':token-retry-456'));
        $this->assertSame(0, Order::where('user_id', $customer->id)->count());

        // Setelah keranjang diperbaiki, token yang sama bisa dipakai lagi
        $item->update(['quantity' => 1]);

        $this->actingAs($customer)->postJson(route('checkout.store'), $payload)->assertOk();
        $this->assertSame(1, Order::where('user_id', $customer->id)->count());
    }
}
