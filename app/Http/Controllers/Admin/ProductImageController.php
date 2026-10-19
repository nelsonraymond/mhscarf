<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Upload and store a new image for the product.
     */
    public function store(
        Request $request,
        Product $product
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

        // Simpan gambar ke Supabase Storage.
        $uploadedPath = $request->file('image')
            ->store('products', 'supabase');

        if (! $uploadedPath) {
            return back()
                ->withErrors([
                    'image' => 'Gagal mengunggah gambar. Silakan coba lagi.',
                ])
                ->withInput();
        }

        $isPrimary = $request->boolean('is_primary')
            || ! $product->images()->exists();

        try {
            if ($isPrimary) {
                $product->images()->update(['is_primary' => false]);
            }

            ProductImage::create([
                'product_id' => $product->id,
                'variant_id' => $validated['variant_id'] ?? null,
                'image_path' => $uploadedPath,
                'alt_text' => $validated['alt_text'] ?? $product->name,
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'is_primary' => $isPrimary,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('supabase')->delete($uploadedPath);

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
        abort_unless($image->product_id === $product->id, 404);

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
        ProductImage $image
    ): RedirectResponse {
        abort_unless($image->product_id === $product->id, 404);

        $wasPrimary = $image->is_primary;
        $imagePath = $image->image_path;

        // Hapus file hanya jika path-nya tersimpan di Supabase.
        $isExternalUrl = preg_match(
            '#^https?://#i',
            (string) $imagePath
        );

        if (! $isExternalUrl && $imagePath) {
            Storage::disk('supabase')->delete($imagePath);
        }

        $image->delete();

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
