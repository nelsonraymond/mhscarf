<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductVariantController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Store a newly created variant for the product.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['required', 'string', 'max:80', 'unique:product_variants,sku'],
            'color_name' => ['nullable', 'string', 'max:80'],
            'color_hex' => ['nullable', 'string', 'max:20'],
            'size' => ['nullable', 'string', 'max:50'],
            'additional_price' => ['nullable', 'numeric', 'min:0'],
            'stock_qty' => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'weight_gram' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        $initialStock = (int) ($validated['stock_qty'] ?? 0);
        $validated['stock_qty'] = 0; // initialize at 0; inventory movement will set to initial stock
        $validated['product_id'] = $product->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['additional_price'] = $validated['additional_price'] ?? 0;

        $variant = ProductVariant::create($validated);

        if ($initialStock > 0) {
            $this->inventoryService->recordMovement(
                variant: $variant,
                quantity: $initialStock,
                type: 'in',
                userId: Auth::id(),
                note: 'Stok awal saat pembuatan varian'
            );
        }

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Varian berhasil ditambahkan.');
    }

    /**
     * Update the specified variant.
     */
    public function update(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless((int) $variant->product_id === (int) $product->id, 404);
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'sku' => ['sometimes', 'required', 'string', 'max:80', Rule::unique('product_variants', 'sku')->ignore($variant->id)],
            'color_name' => ['sometimes', 'nullable', 'string', 'max:80'],
            'color_hex' => ['sometimes', 'nullable', 'string', 'max:20'],
            'size' => ['sometimes', 'nullable', 'string', 'max:50'],
            'additional_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['sometimes', 'integer', 'min:0'],
            'weight_gram' => ['sometimes', 'required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->has('is_active')) {
            $validated['is_active'] = $request->boolean('is_active');
        }
        $variant->update($validated);

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Varian berhasil diperbarui.');
    }

    /** Update only the shipping weight without changing other variant fields. */
    public function updateWeight(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless((int) $variant->product_id === (int) $product->id, 404);
        $validated = $request->validate([
            'weight_gram' => ['required', 'integer', 'min:1'],
        ]);

        $variant->update($validated);

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Berat varian berhasil diperbarui.');
    }

    /**
     * Adjust stock for the specified variant.
     */
   public function updateStock(Request $request, Product $product, ProductVariant $variant): RedirectResponse
{
    $validated = $request->validate([
        'quantity' => ['required', 'integer', 'min:0'],
        'type' => ['required', 'in:in,out,adjustment,return'],
        'note' => ['nullable', 'string', 'max:255'],
        'expected_stock' => ['nullable', 'integer', 'min:0'],
    ]);

    try {
        $this->inventoryService->recordMovement(
            variant: $variant,
            quantity: (int) $validated['quantity'],
            type: $validated['type'],
            userId: Auth::id(),
            note: $validated['note'] ?? 'Penyesuaian stok manual oleh admin',
            expectedStock: $validated['type'] === 'adjustment' && isset($validated['expected_stock'])
                ? (int) $validated['expected_stock']
                : null,
        );
    } catch (\RuntimeException $e) {
        return redirect()->route('admin.products.edit', $product)->with('error', $e->getMessage());
    }

    return redirect()->route('admin.products.edit', $product)
        ->with('success', 'Stok varian berhasil diperbarui.');
}

    /**
     * Remove the specified variant.
     */
    public function destroy(Product $product, ProductVariant $variant): RedirectResponse
    {
        $variant->delete();

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Varian berhasil dihapus.');
    }
}
