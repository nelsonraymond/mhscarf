<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_products_are_displayed_in_public_catalog(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $activeProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Pashmina Sutra Cantik',
            'is_active' => true,
        ]);

        $inactiveProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Produk Nonaktif',
            'is_active' => false,
        ]);

        $response = $this->get(route('shop.index'));

        $response->assertStatus(200);
        $response->assertSee('Pashmina Sutra Cantik');
        $response->assertDontSee('Produk Nonaktif');
    }

    public function test_products_of_inactive_categories_are_hidden(): void
    {
        $inactiveCategory = Category::factory()->create(['is_active' => false]);
        $product = Product::factory()->create([
            'category_id' => $inactiveCategory->id,
            'name' => 'Produk Kategori Nonaktif',
            'is_active' => true,
        ]);

        $response = $this->get(route('shop.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Produk Kategori Nonaktif');
    }

    public function test_catalog_search_filters_products_by_keyword(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Hijab Segi Empat Voal',
            'material' => 'Voal Premium',
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Pashmina Ceruty',
            'material' => 'Ceruty Premium',
            'is_active' => true,
        ]);

        $response = $this->get(route('shop.index', ['q' => 'Voal']));

        $response->assertStatus(200);
        $response->assertSee('Hijab Segi Empat Voal');
        $response->assertDontSee('Pashmina Ceruty');
    }

    public function test_category_filter_returns_only_matching_products(): void
    {
        $catVoal = Category::factory()->create(['name' => 'Voal', 'slug' => 'voal', 'is_active' => true]);
        $catSatin = Category::factory()->create(['name' => 'Satin', 'slug' => 'satin', 'is_active' => true]);

        Product::factory()->create([
            'category_id' => $catVoal->id,
            'name' => 'Voal Ultrafine Laser',
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $catSatin->id,
            'name' => 'Satin Silk Jacquard',
            'is_active' => true,
        ]);

        $response = $this->get(route('shop.category', 'voal'));

        $response->assertStatus(200);
        $response->assertSee('Voal Ultrafine Laser');
        $response->assertDontSee('Satin Silk Jacquard');
    }

    public function test_public_product_detail_page_loads_with_variants_and_stock(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Pashmina Silk Premium',
            'slug' => 'pashmina-silk-premium',
            'is_active' => true,
        ]);

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Dusty Pink',
            'stock_qty' => 15,
            'is_active' => true,
        ]);

        $response = $this->get(route('shop.product', 'pashmina-silk-premium'));

        $response->assertStatus(200);
        $response->assertSee('Pashmina Silk Premium');
        $response->assertSee('Dusty Pink');
        $response->assertSee('Stok Tersedia');
    }

    public function test_inactive_product_detail_returns_404(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'slug' => 'produk-sembunyi',
            'is_active' => false,
        ]);

        $response = $this->get(route('shop.product', 'produk-sembunyi'));

        $response->assertStatus(404);
    }

    public function test_sale_filter_only_shows_discounted_products(): void
{
    $category = Category::factory()->create(['is_active' => true]);

    $discounted = Product::factory()->create([
        'category_id' => $category->id, 'is_active' => true,
        'base_price' => 80000, 'compare_at_price' => 100000,
    ]);
    $regular = Product::factory()->create([
        'category_id' => $category->id, 'is_active' => true,
        'base_price' => 90000, 'compare_at_price' => null,
    ]);

    $this->get(route('shop.index', ['sale' => 1]))
        ->assertOk()
        ->assertSee($discounted->name)
        ->assertDontSee($regular->name);
}

public function test_best_alias_sorts_like_best_seller(): void
{
    $category = Category::factory()->create(['is_active' => true]);

    Product::factory()->create(['category_id' => $category->id, 'is_active' => true, 'is_best_seller' => false, 'name' => 'Produk Biasa']);
    Product::factory()->create(['category_id' => $category->id, 'is_active' => true, 'is_best_seller' => true, 'name' => 'Produk Andalan']);

    $this->get(route('shop.index', ['sort' => 'best']))
        ->assertOk()
        ->assertSeeInOrder(['Produk Andalan', 'Produk Biasa']);
}
}
