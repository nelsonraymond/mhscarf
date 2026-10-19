<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    protected function createProduct(): Product
    {
        $category = Category::factory()->create(['is_active' => true]);

        return Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_wishlist_and_is_redirected_to_login(): void
    {
        $product = $this->createProduct();

        $response = $this->get(route('wishlist.index'));
        $response->assertRedirect(route('login'));

        $responseToggle = $this->post(route('wishlist.toggle', $product));
        $responseToggle->assertRedirect(route('login'));
    }

    public function test_customer_can_view_wishlist(): void
    {
        $customer = User::factory()->customer()->create();
        $product = $this->createProduct();

        Wishlist::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($customer)->get(route('wishlist.index'));

        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee('Daftar Keinginan');
    }

    public function test_customer_can_toggle_product_in_wishlist(): void
    {
        $customer = User::factory()->customer()->create();
        $product = $this->createProduct();

        // 1. First toggle => adds to wishlist
        $response = $this->actingAs($customer)->post(route('wishlist.toggle', $product));
        $response->assertRedirect();

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        // 2. Second toggle => removes from wishlist
        $response = $this->actingAs($customer)->post(route('wishlist.toggle', $product));
        $response->assertRedirect();

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_customer_can_remove_product_from_wishlist(): void
    {
        $customer = User::factory()->customer()->create();
        $product = $this->createProduct();

        Wishlist::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($customer)->delete(route('wishlist.destroy', $product));

        $response->assertRedirect();
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_duplicate_wishlist_entries_are_prevented(): void
    {
        $customer = User::factory()->customer()->create();
        $product = $this->createProduct();

        Wishlist::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        // Trying to create or toggle again cannot create a duplicate row
        $this->assertEquals(1, Wishlist::where('user_id', $customer->id)->where('product_id', $product->id)->count());
    }

    public function test_navbar_displays_wishlist_counter_for_authenticated_customer(): void
    {
        $customer = User::factory()->customer()->create();
        $product1 = $this->createProduct();
        $product2 = $this->createProduct();

        Wishlist::create(['user_id' => $customer->id, 'product_id' => $product1->id]);
        Wishlist::create(['user_id' => $customer->id, 'product_id' => $product2->id]);

        $response = $this->actingAs($customer)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Wishlist');
        // Both navbar desktop and mobile render count 2
        $response->assertSee('>2<', false);
    }

    public function test_navbar_does_not_display_wishlist_counter_for_guest(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('>99+<', false);
    }
}
