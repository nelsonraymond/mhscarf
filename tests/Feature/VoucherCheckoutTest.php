<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherCheckoutTest extends TestCase
{
    use RefreshDatabase;

    /** Satu pembeli, satu varian Rp100.000 (stok 10), keranjang berisi 1 item. */
    private function setUpCheckout(int $stock = 10): array
    {
        $customer = User::factory()->customer()->create();
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'base_price' => 100000,
            'is_active' => true,
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'additional_price' => 0,
            'stock_qty' => $stock,
            'is_active' => true,
        ]);
        $address = Address::factory()->create(['user_id' => $customer->id, 'is_default' => true]);

        $cart = Cart::create(['user_id' => $customer->id]);
        $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 1, 'unit_price' => 100000]);

        return [$customer, $variant, $address];
    }

    private function makeVoucher(array $attrs = []): Voucher
    {
        return Voucher::create(array_merge([
            'code' => 'HEMAT10',
            'type' => 'percentage',
            'value' => 10,
            'max_discount_amount' => 20000,
            'is_active' => true,
        ], $attrs));
    }

    private function checkout(User $customer, Address $address, ?string $code, string $shipping = 'regular')
    {
        return $this->actingAs($customer)->postJson(route('checkout.store'), array_filter([
            'address_id' => $address->id,
            'shipping_method' => $shipping,
            'voucher_code' => $code,
        ]));
    }

    public function test_preview_returns_discount_and_new_total(): void
    {
        [$customer] = $this->setUpCheckout();
        $this->makeVoucher();

        $this->actingAs($customer)
            ->postJson(route('checkout.voucher'), ['voucher_code' => 'hemat10', 'shipping_method' => 'regular'])
            ->assertOk()
            ->assertJsonPath('code', 'HEMAT10')
            ->assertJsonPath('discount', 10000)
            ->assertJsonPath('total', 105000); // 100.000 + 15.000 ongkir - 10.000
    }

    public function test_checkout_applies_voucher_and_records_usage(): void
    {
        [$customer, $variant, $address] = $this->setUpCheckout();
        $voucher = $this->makeVoucher();

        $this->checkout($customer, $address, 'HEMAT10')->assertOk();

        $order = Order::where('user_id', $customer->id)->firstOrFail();
        $this->assertSame('HEMAT10', $order->voucher_code);
        $this->assertEquals(10000, $order->discount_amount);
        $this->assertEquals(10000, $order->voucher_discount);
        $this->assertEquals(105000, $order->grand_total);

        $this->assertDatabaseHas('voucher_usages', [
            'voucher_id' => $voucher->id,
            'user_id' => $customer->id,
            'order_id' => $order->id,
        ]);
        $this->assertSame(1, $voucher->fresh()->used_count);
    }

    public function test_free_shipping_voucher_covers_shipping_cost(): void
    {
        [$customer, , $address] = $this->setUpCheckout();
        $this->makeVoucher(['code' => 'ONGKIR', 'type' => 'free_shipping', 'value' => 0, 'max_discount_amount' => null]);

        $this->checkout($customer, $address, 'ONGKIR', 'express')->assertOk();

        $order = Order::where('user_id', $customer->id)->firstOrFail();
        $this->assertEquals(30000, $order->discount_amount);
        $this->assertEquals(100000, $order->grand_total); // 100.000 + 30.000 - 30.000
    }

    public function test_voucher_below_minimum_order_is_rejected_without_side_effects(): void
    {
        [$customer, $variant, $address] = $this->setUpCheckout();
        $voucher = $this->makeVoucher(['min_order_amount' => 200000]);

        $this->actingAs($customer)
            ->postJson(route('checkout.voucher'), ['voucher_code' => 'HEMAT10', 'shipping_method' => 'regular'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('voucher_code');

        // Checkout dengan voucher yang tidak memenuhi syarat harus gagal dan membatalkan semuanya
        $this->checkout($customer, $address, 'HEMAT10')->assertStatus(422);

        $this->assertSame(0, Order::where('user_id', $customer->id)->count());
        $this->assertEquals(10, $variant->fresh()->stock_qty);
        $this->assertSame(0, $voucher->fresh()->used_count);
    }

    public function test_inactive_expired_and_scheduled_vouchers_are_rejected(): void
    {
        [$customer, , $address] = $this->setUpCheckout();

        $this->makeVoucher(['code' => 'MATI', 'is_active' => false]);
        $this->makeVoucher(['code' => 'LEWAT', 'expires_at' => now()->subDay()]);
        $this->makeVoucher(['code' => 'NANTI', 'starts_at' => now()->addDay()]);

        foreach (['MATI', 'LEWAT', 'NANTI', 'TIDAKADA'] as $code) {
            $this->checkout($customer, $address, $code)->assertStatus(422);
        }

        $this->assertSame(0, Order::count());
    }

    public function test_voucher_with_exhausted_quota_is_rejected(): void
    {
        [$customer, , $address] = $this->setUpCheckout();
        $this->makeVoucher(['usage_limit' => 1, 'used_count' => 1]);

        $this->checkout($customer, $address, 'HEMAT10')->assertStatus(422);

        $this->assertSame(0, Order::count());
    }

    public function test_per_user_limit_blocks_second_use(): void
    {
        [$customer, $variant, $address] = $this->setUpCheckout();
        $this->makeVoucher(['usage_limit_per_user' => 1]);

        $this->checkout($customer, $address, 'HEMAT10')->assertOk();

        // Isi keranjang lagi, lalu coba pakai voucher yang sama
        Cart::where('user_id', $customer->id)->firstOrFail()->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 100000,
        ]);

        $this->checkout($customer, $address, 'HEMAT10')->assertStatus(422);

        $this->assertSame(1, Order::where('user_id', $customer->id)->count());
    }

    public function test_cancelling_order_returns_voucher_quota(): void
    {
        [$customer, , $address] = $this->setUpCheckout();
        $voucher = $this->makeVoucher(['usage_limit' => 5]);

        $this->checkout($customer, $address, 'HEMAT10')->assertOk();
        $order = Order::where('user_id', $customer->id)->firstOrFail();
        $this->assertSame(1, $voucher->fresh()->used_count);

        $this->actingAs($customer)->post(route('orders.cancel', $order));

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(0, $voucher->fresh()->used_count);
        $this->assertDatabaseMissing('voucher_usages', ['order_id' => $order->id]);
    }

    public function test_checkout_without_voucher_is_unchanged(): void
    {
        [$customer, , $address] = $this->setUpCheckout();

        $this->checkout($customer, $address, null)->assertOk();

        $order = Order::where('user_id', $customer->id)->firstOrFail();
        $this->assertNull($order->voucher_code);
        $this->assertEquals(0, $order->discount_amount);
        $this->assertEquals(115000, $order->grand_total);
    }
}