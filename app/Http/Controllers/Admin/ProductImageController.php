<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\CloudinaryService;

class ProductImageController extends Controller
{
    /**
     * Upload and store a new image for the product.
     */
    public function store(
    Request $request,
    Product $product,
    CloudinaryService $cloudinary
): RedirectResponse {
    $validated = $request->validate([
        'image' => [
            'required',
            'file',
            'image',
            'mimes:jpeg,jpg,png,webp',
            'max:5120',
        ],
        'variant_id' => ['nullable', 'exists:product_variants,id'],
        'alt_text' => ['nullable', 'string', 'max:255'],
        'sort_order' => ['nullable', 'integer', 'min:0'],
        'is_primary' => ['sometimes', 'boolean'],
    ]);

    // Pastikan varian yang dipilih memang milik produk ini.
    if (! empty($validated['variant_id'])) {
        $variantBelongsToProduct = $product->variants()
            ->whereKey($validated['variant_id'])
            ->exists();

        if (! $variantBelongsToProduct) {
            return back()
                ->withErrors([
                    'variant_id' => 'Varian tidak sesuai dengan produk ini.',
                ])
                ->withInput();
        }
    }

    $uploaded = $cloudinary->upload(
        $request->file('image'),
        'mh-scarf/products'
    );

    $isPrimary = $request->boolean('is_primary')
        || ! $product->images()->exists();

    try {
        if ($isPrimary) {
            $product->images()->update(['is_primary' => false]);
        }

        ProductImage::create([
            'product_id' => $product->id,
            'variant_id' => $validated['variant_id'] ?? null,
            'image_path' => $uploaded['url'],
            'cloudinary_public_id' => $uploaded['public_id'],
            'alt_text' => $validated['alt_text'] ?? $product->name,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_primary' => $isPrimary,
        ]);
    } catch (\Throwable $exception) {
        // Jangan tinggalkan gambar baru di Cloudinary jika penyimpanan gagal.
        $cloudinary->delete($uploaded['public_id']);

        throw $exception;
    }

    return redirect()
        ->route('admin.products.edit', $product)
        ->with('success', 'Gambar produk berhasil diunggah.');
}

    /**
     * Set the image as the primary image for the product.
     */
    public function setPrimary(
    Product $product,
    ProductImage $image
): RedirectResponse {
    abort_unless(
        $image->product_id === $product->id,
        404
    );

    $product->images()->update(['is_primary' => false]);
    $image->update(['is_primary' => true]);

    return redirect()
        ->route('admin.products.edit', $product)
        ->with('success', 'Gambar utama berhasil diperbarui.');
}

    /**
     * Delete the specified product image.
     */
    public function destroy(
    Product $product,
    ProductImage $image,
    CloudinaryService $cloudinary
): RedirectResponse {
    abort_unless(
        $image->product_id === $product->id,
        404
    );

    $wasPrimary = $image->is_primary;
    $publicId = $image->cloudinary_public_id;
    $imagePath = $image->image_path;

    $image->delete();

    // Hapus aset dari Cloudinary, atau file lokal lama jika belum dimigrasikan.
    if ($publicId) {
        $cloudinary->delete($publicId);
    } elseif (
        $imagePath
        && ! str_starts_with($imagePath, 'http://')
        && ! str_starts_with($imagePath, 'https://')
    ) {
        Storage::disk('public')->delete($imagePath);
    }

    if ($wasPrimary) {
        $product->images()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first()
            ?->update(['is_primary' => true]);
    }

    return redirect()
        ->route('admin.products.edit', $product)
        ->with('success', 'Gambar produk berhasil dihapus.');
}
}
