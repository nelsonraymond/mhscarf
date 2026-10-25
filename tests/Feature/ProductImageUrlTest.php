<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductImageUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.supabase.url' => 'https://project.supabase.co/storage/v1/object/public/mh-scarf-images']);
    }

    public function test_product_image_accessor_builds_public_bucket_url_and_preserves_external_urls(): void
    {
        $image = new ProductImage(['image_path' => 'products/satin-front.webp']);

        $this->assertSame(
            'https://project.supabase.co/storage/v1/object/public/mh-scarf-images/products/satin-front.webp',
            $image->image_url,
        );

        $externalImage = new ProductImage(['image_path' => 'https://cdn.example.test/satin.webp']);
        $this->assertSame('https://cdn.example.test/satin.webp', $externalImage->image_url);
    }

    public function test_product_image_accessor_returns_null_for_empty_path(): void
    {
        $this->assertNull((new ProductImage(['image_path' => '']))->image_url);
        $this->assertNull((new ProductImage)->image_url);
    }

    public function test_detail_gallery_renders_accessor_urls_and_a_broken_image_fallback(): void
    {
        $product = $this->makeProduct('satin-gallery');
        $product->images()->create([
            'image_path' => 'products/satin-front.webp', 'is_primary' => true, 'sort_order' => 0,
        ]);
        $product->images()->create([
            'image_path' => 'products/satin-back.webp', 'is_primary' => false, 'sort_order' => 1,
        ]);

        $response = $this->get(route('shop.product', $product->slug));

        $response->assertOk();
        $response->assertSee('https://project.supabase.co/storage/v1/object/public/mh-scarf-images/products/satin-front.webp');
        $response->assertSee('https://project.supabase.co/storage/v1/object/public/mh-scarf-images/products/satin-back.webp');
        $response->assertSee('data-image-fallback', false);
        $response->assertDontSee('/storage/https://project.supabase.co');
    }

    public function test_empty_product_gallery_keeps_the_existing_placeholder(): void
    {
        $product = $this->makeProduct('without-image');

        $this->get(route('shop.product', $product->slug))
            ->assertOk()
            ->assertSee('Foto '.$product->name.' segera hadir');
    }

    public function test_shop_catalog_uses_the_same_public_image_url(): void
    {
        $product = $this->makeProduct('catalog-image');
        $product->images()->create([
            'image_path' => 'products/catalog.webp', 'is_primary' => true,
        ]);

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('https://project.supabase.co/storage/v1/object/public/mh-scarf-images/products/catalog.webp');
    }

    public function test_category_images_keep_the_bucket_url_and_have_a_neutral_fallback(): void
    {
        Category::factory()->create([
            'slug' => 'satin',
            'image' => 'categories/satin.webp',
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://project.supabase.co/storage/v1/object/public/mh-scarf-images/categories/satin.webp')
            ->assertSee('data-image-fallback', false);
    }

    public function test_best_seller_navigation_is_plain_and_active_on_desktop_and_mobile(): void
    {
        $url = route('shop.index', ['sort' => 'best']);

        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('Best Seller');
        $response->assertSee('aria-current="page"', false);

        $document = new \DOMDocument;
        libxml_use_internal_errors(true);
        $document->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXPath($document);
        $desktopLink = $xpath->query("//nav[@aria-label='Navigasi utama']/a[@href='{$url}' and normalize-space(.)='Best Seller']");
        $mobileLink = $xpath->query("//nav[@aria-label='Navigasi mobile']/a[@href='{$url}' and normalize-space(.)='Best Seller']");
        $navBadges = $xpath->query("//nav[contains(@aria-label, 'Navigasi')]//span[contains(normalize-space(.), 'Best Seller')]");

        $this->assertSame(1, $desktopLink->length);
        $this->assertSame(1, $mobileLink->length);
        $this->assertSame(0, $navBadges->length);
    }

    private function makeProduct(string $slug): Product
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'slug' => $slug,
            'is_active' => true,
        ]);

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock_qty' => 5,
            'is_active' => true,
        ]);

        return $product;
    }
}
