<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Order;
use App\Services\PaymentService;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Concerns\InteractsWithShippingQuotes;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithShippingQuotes;

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

    public function test_checkout_requires_a_valid_uuid_token(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)->postJson(route('checkout.store'), [
            'address_id' => 1,
            'shipping_quote_token' => 'quote',
        ])->assertUnprocessable()->assertJsonValidationErrors('checkout_token');

        $this->actingAs($customer)->postJson(route('checkout.store'), [
            'address_id' => 1,
            'shipping_quote_token' => 'quote',
            'checkout_token' => 'not-a-uuid',
        ])->assertUnprocessable()->assertJsonValidationErrors('checkout_token');
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
        $response->assertViewHas('shippingCost', 0);
        $response->assertViewHas('grandTotal', 130000.0);
        $response->assertSee($address->recipient_name);
        $response->assertSee('Hitung ongkir');
        $this->assertMatchesRegularExpression('/<button id="checkout-submit"[^>]*\sdisabled(?:\s|>)/', $response->getContent());
        $response->assertSee('Pilih alamat, kurir, lalu hitung ongkir untuk melanjutkan.');
        $response->assertSee('checkout-state.js', false);
        $this->assertTrue(\Illuminate\Support\Str::isUuid($response->getOriginalContent()->getData()['checkoutToken']));
    }

    public function test_buy_now_keeps_the_same_uuid_checkout_token_across_page_retries(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant(stock: 5);

        $this->actingAs($customer)->post(route('buy.now'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect(route('checkout.index'));

        $firstPage = $this->get(route('checkout.index'))->assertOk();
        $firstToken = $firstPage->getOriginalContent()->getData()['checkoutToken'];
        $secondPage = $this->get(route('checkout.index'))->assertOk();
        $secondToken = $secondPage->getOriginalContent()->getData()['checkoutToken'];

        $this->assertTrue(\Illuminate\Support\Str::isUuid($firstToken));
        $this->assertSame($firstToken, $secondToken);
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
            'checkout_token' => (string) \Illuminate\Support\Str::uuid(),
            'shipping_quote_token' => 'test-shipping-quote',
        ]);

        $response->assertSessionHasErrors('cart');
    }

        public function test_duplicate_checkout_token_returns_same_order(): void
    {
        // Idempotency belongs to orders. Carts intentionally has no checkout_token column.
        $this->assertTrue(Schema::hasColumn('orders', 'checkout_token'));
        $this->assertFalse(Schema::hasColumn('carts', 'checkout_token'));

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
            'shipping_quote_token' => $this->fakeShippingQuote(),
            'checkout_token' => '11111111-1111-4111-8111-111111111111',
        ];

        $first = $this->actingAs($customer)->postJson(route('checkout.store'), $payload)->assertOk();
        // Simulate a request timeout after DB commit but before cache was updated.
        Cache::put('checkout-idem:'.$customer->id.':11111111-1111-4111-8111-111111111111', 'processing', now()->addMinutes(10));
        $second = $this->actingAs($customer)->postJson(route('checkout.store'), $payload)->assertOk();

        // Token yang sama mengembalikan pesanan yang sama, bukan membuat pesanan baru
        $this->assertSame($first->json('order_id'), $second->json('order_id'));
        $this->assertSame(1, Order::where('user_id', $customer->id)->count());
        $this->assertSame('11111111-1111-4111-8111-111111111111', Order::find($first->json('order_id'))->checkout_token);

        // Stok hanya terpotong sekali
        $this->assertEquals(9, $variant->fresh()->stock_qty);

        $changedPayload = $payload + ['customer_note' => 'Payload operasi berbeda'];
        $this->actingAs($customer)->postJson(route('checkout.store'), $changedPayload)->assertStatus(409);
        $this->assertSame(1, Order::where('user_id', $customer->id)->count());
        $this->assertEquals(9, $variant->fresh()->stock_qty);

        $otherVariant = $this->createVariant(stock: 5);
        $cart = Cart::where('user_id', $customer->id)->firstOrFail();
        $cart->items()->create(['product_variant_id' => $otherVariant->id, 'quantity' => 1, 'unit_price' => 60000]);
        $this->actingAs($customer)->postJson(route('checkout.store'), $payload)->assertStatus(409);
        $this->assertEquals(5, $otherVariant->fresh()->stock_qty);
    }

    public function test_duplicate_database_key_recovers_committed_order_after_failed_attempt_rolls_back(): void
    {
        $customer = User::factory()->customer()->create();
        $variant = $this->createVariant(stock: 10);
        $address = Address::factory()->create(['user_id' => $customer->id]);
        $cart = Cart::create(['user_id' => $customer->id]);
        $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 1, 'unit_price' => 60000]);
        $token = '55555555-5555-4555-8555-555555555555';
        $quoteToken = $this->fakeShippingQuote();
        $fingerprint = hash('sha256', json_encode([
            'address_id' => (int) $address->id,
            'voucher_code' => '',
            'shipping_quote_token' => hash('sha256', $quoteToken),
            'customer_note' => '',
        ], JSON_THROW_ON_ERROR));

        $checkout = $this->mock(CheckoutService::class);
        $checkout->shouldReceive('currentShippingWeight')->once()->andReturn(150);
        $checkout->shouldReceive('currentShippingFingerprint')->once()->andReturn('test-fingerprint');
        $checkout->shouldReceive('matchesExistingOrderCart')->once()->andReturn(true);
        $checkout->shouldReceive('placeOrder')->once()->andReturnUsing(function () use ($customer, $variant, $token, $fingerprint) {
            // Simulate another instance committing first and deducting its stock once.
            Order::factory()->create([
                'user_id' => $customer->id,
                'checkout_token' => $token,
                'checkout_fingerprint' => $fingerprint,
                'checkout_cart_fingerprint' => hash('sha256', json_encode([
                    ['variant_id' => (int) $variant->id, 'quantity' => 1],
                ], JSON_THROW_ON_ERROR)),
            ]);
            $variant->decrement('stock_qty');

            // The losing transaction's stock movement and duplicate insert must roll back.
            DB::transaction(function () use ($customer, $variant, $token) {
                $variant->decrement('stock_qty');
                Order::factory()->create(['user_id' => $customer->id, 'checkout_token' => $token]);
            });
        });

        $response = $this->actingAs($customer)->postJson(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => $quoteToken,
            'checkout_token' => $token,
        ])->assertOk();

        $this->assertSame(1, Order::where('user_id', $customer->id)->count());
        $this->assertSame($token, Order::where('user_id', $customer->id)->value('checkout_token'));
        $this->assertEquals(9, $variant->fresh()->stock_qty);
        $this->assertSame(Order::where('user_id', $customer->id)->value('id'), $response->json('order_id'));
    }

    public function test_payment_initialization_server_error_does_not_expose_exception_or_duplicate_order(): void
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

        $this->mock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('initializePayment')->once()->andThrow(new \RuntimeException('gateway secret detail'));
        });

        $payload = [
            'address_id' => $address->id,
            'shipping_quote_token' => $this->fakeShippingQuote(),
            'checkout_token' => '22222222-2222-4222-8222-222222222222',
        ];

        $response = $this->actingAs($customer)->postJson(route('checkout.store'), $payload)
            ->assertStatus(500)
            ->assertJsonMissing(['error' => 'gateway secret detail'])
            ->assertJsonStructure(['error', 'order_id']);

        $this->assertSame(1, Order::where('user_id', $customer->id)->count());
        $this->assertSame(9, $variant->fresh()->stock_qty);
        $this->assertSame('22222222-2222-4222-8222-222222222222', Order::find($response->json('order_id'))->checkout_token);
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
            'shipping_quote_token' => $this->fakeShippingQuote(),
            'checkout_token' => '33333333-3333-4333-8333-333333333333',
        ];

        $this->actingAs($customer)->postJson(route('checkout.store'), $payload)->assertStatus(422);

        // Pesanan gagal dibuat, jadi token harus dilepas
        $this->assertFalse(Cache::has('checkout-idem:'.$customer->id.':33333333-3333-4333-8333-333333333333'));
        $this->assertSame(0, Order::where('user_id', $customer->id)->count());

        // Setelah keranjang diperbaiki, token yang sama bisa dipakai lagi
        $item->update(['quantity' => 1]);
        $payload['shipping_quote_token'] = $this->fakeShippingQuote();

        $this->actingAs($customer)->postJson(route('checkout.store'), $payload)->assertOk();
        $this->assertSame(1, Order::where('user_id', $customer->id)->count());
    }
}
