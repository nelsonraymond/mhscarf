<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Concerns\InteractsWithShippingQuotes;

/**
 * Integration test to verify that a cart created by a guest is correctly
 * processed after the guest logs in, and that the checkout flow validates
 * the cart contents, updates stock, creates an order, and empties the cart.
 */
class CartCheckoutIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithShippingQuotes;

    /**
     * Helper to create an active product variant with given stock and price.
     */
    protected function createActiveVariant(int $stock = 10, float $basePrice = 50000, float $addPrice = 0): ProductVariant
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

    /** @test */
    public function test_guest_cart_is_preserved_and_used_after_login_during_checkout()
    {
        // 1. Guest adds a variant to the cart.
        $variant = $this->createActiveVariant(stock: 5, basePrice: 60000, addPrice: 5000);

        $guestResponse = $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $guestResponse->assertRedirect(route('cart.index'));

        // Capture the session cart id generated for the guest.
        $guestCartId = session('cart_id');
        $this->assertNotNull($guestCartId, 'Guest cart id should be stored in session');

        $guestCart = Cart::where('id', $guestCartId)->first();
        $this->assertNotNull($guestCart, 'Guest cart should exist in DB');
        $this->assertNull($guestCart->user_id, 'Guest cart is not linked to a user');
        $this->assertEquals(2, $guestCart->items->first()->quantity);

        // 2. Customer registers / logs in.
        $customer = User::factory()->customer()->create();
        $this->actingAs($customer); // keep the same session (cart_id stays)
        // Merge the guest cart into the authenticated user's cart
        $this->app->make(CartService::class)->mergeGuestCart(session()->getId(), $customer, $guestCartId);

        // 3. Ensure checkout page loads with the merged cart.
        $checkoutPage = $this->get(route('checkout.index'));
        $checkoutPage->assertOk();
        $checkoutPage->assertViewHas('cart');
        $checkoutPage->assertViewHas('subtotal');

        // 4. Provide a shipping address for the customer.
        $address = Address::factory()->create([
            'user_id' => $customer->id,
            'is_default' => true,
        ]);

        // 5. Perform checkout via JSON request to receive the API payload.
        $checkoutResponse = $this->postJson(route('checkout.store'), [
            'address_id' => $address->id,
            'checkout_token' => (string) \Illuminate\Support\Str::uuid(),
            'shipping_quote_token' => $this->fakeShippingQuote(weight: 300),
        ]);
        $checkoutResponse->assertOk();
        $checkoutResponse->assertJsonStructure([
            'order_id',
            'order_number',
            'snap_token',
            'client_key',
        ]);

        // 6. Verify order creation and its details.
        $order = Order::first();
        $this->assertNotNull($order, 'An order should be created');
        $this->assertEquals($customer->id, $order->user_id);
        $this->assertCount(1, $order->items);
        $orderItem = $order->items->first();
        $this->assertEquals($variant->id, $orderItem->product_variant_id);
        $this->assertEquals(2, $orderItem->quantity);

        // Expected subtotal: variant final price (base + additional) * qty
        $expectedUnit = $variant->finalPrice();
        $expectedSubtotal = $expectedUnit * 2;
        $this->assertEquals($expectedSubtotal, $order->subtotal);

        // 7. Stock should be deducted.
        $this->assertEquals(5 - 2, $variant->fresh()->stock_qty);

        // 8. Cart must be cleared after successful order.
        $userCart = Cart::where('user_id', $customer->id)->first();
        $this->assertNotNull($userCart, 'User cart should exist after checkout');
        $this->assertTrue($userCart->fresh()->items->isEmpty(), 'User cart should be empty after checkout');
    }

    /** @test */
    public function test_checkout_fails_when_cart_is_empty()
    {
        $customer = User::factory()->customer()->create();
        $this->actingAs($customer);
        $address = Address::factory()->create(['user_id' => $customer->id]);

        $response = $this->postJson(route('checkout.store'), [
            'address_id' => $address->id,
            'checkout_token' => (string) \Illuminate\Support\Str::uuid(),
            'shipping_quote_token' => $this->fakeShippingQuote(),
        ]);
        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => ['cart' => ['Keranjang belanja Anda masih kosong.']]]);
    }

    /** @test */
    public function test_users_cannot_access_or_manipulate_others_cart_or_order()
    {
        // Customer A adds a product.
        $variant = $this->createActiveVariant();
        $customerA = User::factory()->customer()->create();
        $this->actingAs($customerA);
        $this->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);
        $cartA = Cart::where('user_id', $customerA->id)->first();
        $this->assertNotNull($cartA);
        $addressA = Address::factory()->create(['user_id' => $customerA->id]);
        $orderResponse = $this->postJson(route('checkout.store'), [
            'address_id' => $addressA->id,
            'checkout_token' => (string) \Illuminate\Support\Str::uuid(),
            'shipping_quote_token' => $this->fakeShippingQuote(),
        ]);
        $orderResponse->assertOk();
        $orderA = Order::first();

        // Customer B attempts to fetch the order page of Customer A.
        $customerB = User::factory()->customer()->create();
        $this->actingAs($customerB);
        $orderShow = $this->get(route('payments.show', $orderA));
        $orderShow->assertStatus(403);
    }
}
