<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Pashmina Ceruty Premium',
            'slug' => '',
            'sku' => 'PAS-001',
            'description' => 'Bahan jatuh dan nyaman',
            'material' => 'Ceruty Babydoll',
            'base_price' => 75000,
            'compare_at_price' => 95000,
            'is_featured' => true,
            'is_best_seller' => false,
            'is_active' => true,
        ]);

        $product = Product::where('sku', 'PAS-001')->first();
        $this->assertNotNull($product);

        $response->assertRedirect(route('admin.products.edit', $product));

        $this->assertDatabaseHas('products', [
            'sku' => 'PAS-001',
            'name' => 'Pashmina Ceruty Premium',
            'slug' => 'pashmina-ceruty-premium',
            'base_price' => 75000,
        ]);
    }

    public function test_duplicate_product_sku_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Product::factory()->create(['sku' => 'EXISTING-SKU']);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Produk Lain',
            'sku' => 'EXISTING-SKU',
            'base_price' => 50000,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors(['sku']);
    }

    public function test_admin_can_update_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['name' => 'Nama Lama']);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $product->category_id,
            'name' => 'Nama Baru Cantik',
            'slug' => 'nama-baru-cantik',
            'sku' => $product->sku,
            'base_price' => 89000,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.products.edit', $product));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Nama Baru Cantik',
            'base_price' => 89000,
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_customer_cannot_access_admin_product_pages(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('admin.products.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($customer)->get(route('admin.products.create'));
        $response->assertStatus(403);
    }

    public function test_admin_can_create_variant_and_stock_is_recorded_in_inventory_movements(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.products.variants.store', $product), [
            'name' => 'Rose Pink',
            'sku' => 'VAR-RP-01',
            'color_name' => 'Rose Pink',
            'color_hex' => '#D98FAF',
            'size' => 'All Size',
            'stock_qty' => 25,
            'additional_price' => 0,
            'weight_gram' => 150,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.products.edit', $product));

        $variant = ProductVariant::where('sku', 'VAR-RP-01')->first();
        $this->assertNotNull($variant);
        $this->assertEquals(25, $variant->stock_qty);
        $this->assertEquals(150, $variant->weight_gram);

        // Verify inventory movement audit record
        $this->assertDatabaseHas('inventory_movements', [
            'product_variant_id' => $variant->id,
            'type' => 'in',
            'quantity' => 25,
            'created_by' => $admin->id,
        ]);
    }

    public function test_admin_can_adjust_variant_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock_qty' => 10,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.products.variants.stock', [$product, $variant]), [
            'type' => 'in',
            'quantity' => 15,
            'note' => 'Restock barang masuk',
        ]);

        $response->assertRedirect(route('admin.products.edit', $product));

        $variant->refresh();
        $this->assertEquals(25, $variant->stock_qty);

        $this->assertDatabaseHas('inventory_movements', [
            'product_variant_id' => $variant->id,
            'type' => 'in',
            'quantity' => 15,
            'note' => 'Restock barang masuk',
        ]);
    }
}
