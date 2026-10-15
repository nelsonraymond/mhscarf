<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_categories(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Pashmina']);

        $response = $this->actingAs($admin)->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Pashmina');
        $response->assertSee('Daftar Kategori');
    }

    public function test_admin_can_create_category_with_auto_slug(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Hijab Instan',
            'slug' => '',
            'description' => 'Kategori hijab instan praktis',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Hijab Instan',
            'slug' => 'hijab-instan',
            'is_active' => true,
        ]);
    }

    private function createFakeImage(string $filename = 'test.png'): UploadedFile
    {
        $png1x1 = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent($filename, $png1x1);
    }

    public function test_admin_can_create_category_with_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $file = $this->createFakeImage('pashmina.png');

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Pashmina Silk',
            'slug' => 'pashmina-silk',
            'image' => $file,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $category = Category::where('slug', 'pashmina-silk')->firstOrFail();
        $this->assertNotNull($category->image);
        Storage::disk('public')->assertExists($category->image);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create([
            'name' => 'Voal Lama',
            'slug' => 'voal-lama',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Voal Premium',
            'slug' => 'voal-premium',
            'description' => 'Deskripsi baru',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Voal Premium',
            'slug' => 'voal-premium',
        ]);
    }

    public function test_admin_can_update_category_with_new_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $oldFile = $this->createFakeImage('old-category.png');
        $oldPath = $oldFile->store('categories', 'public');
        $category = Category::factory()->create([
            'name' => 'Segi Empat',
            'slug' => 'segi-empat',
            'image' => $oldPath,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        $newFile = $this->createFakeImage('new-category.png');
        $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Segi Empat Premium',
            'slug' => 'segi-empat-premium',
            'image' => $newFile,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $category->refresh();
        $this->assertNotEquals($oldPath, $category->image);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($category->image);
    }

    public function test_admin_can_remove_category_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $oldFile = $this->createFakeImage('old-category.png');
        $oldPath = $oldFile->store('categories', 'public');
        $category = Category::factory()->create([
            'name' => 'Bergo',
            'slug' => 'bergo',
            'image' => $oldPath,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Bergo Maryam',
            'slug' => 'bergo-maryam',
            'remove_image' => '1',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $category->refresh();
        $this->assertNull($category->image);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_admin_cannot_upload_invalid_category_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Koleksi Baru',
            'slug' => 'koleksi-baru',
            'image' => $file,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors(['image']);
    }

    public function test_category_cannot_be_its_own_parent(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => $category->name,
            'slug' => $category->slug,
            'parent_id' => $category->id,
        ]);

        $response->assertSessionHasErrors(['parent_id']);
    }

    public function test_admin_can_delete_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_customer_cannot_access_admin_category_pages(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('admin.categories.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($customer)->get(route('admin.categories.create'));
        $response->assertStatus(403);
    }
}
