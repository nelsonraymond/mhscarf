<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Display the shopping cart.
     */
    public function index(Request $request): View
    {
        $request->session()->forget(['buy_now', 'checkout_source']);
        
        $cart = $this->cartService->getCart(
            $request->user(),
            $request->session()->getId(),
            $request->session()->get('cart_id')
        );

        if ($cart) {
            $cart->load([
                'items.variant.product.images',
                'items.variant.product.category',
            ]);
        }

        return view('cart.index', compact('cart'));
    }

    /**
     * Add a variant to the cart.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId(),
            $request->session()->get('cart_id')
        );

        if (! $request->user()) {
            $request->session()->put('cart_id', $cart->id);
        }

        $variant = ProductVariant::with('product')->findOrFail($validated['product_variant_id']);

        $this->cartService->addItem($cart, $variant, (int) $validated['quantity']);

        return redirect()->route('cart.index')
            ->with('success', 'Produk berhasil ditambahkan ke keranjang belanja.');
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        if (! $this->cartService->authorizeItem($cartItem, $request->user(), $request->session()->getId(), $request->session()->get('cart_id'))) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $this->cartService->updateItemQuantity($cartItem, (int) $validated['quantity']);

        return redirect()->route('cart.index')
            ->with('success', 'Kuantitas keranjang berhasil diperbarui.');
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        if (! $this->cartService->authorizeItem($cartItem, $request->user(), $request->session()->getId(), $request->session()->get('cart_id'))) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        $this->cartService->removeItem($cartItem);

        return redirect()->route('cart.index')
            ->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    /**
     * Clear all items in the current cart.
     */
    public function clear(Request $request): RedirectResponse
    {
        $cart = $this->cartService->getCart(
            $request->user(),
            $request->session()->getId(),
            $request->session()->get('cart_id')
        );

        if ($cart) {
            $this->cartService->clearCart($cart);
            $request->session()->forget('cart_id');
        }

        return redirect()->route('cart.index')
            ->with('success', 'Keranjang belanja telah dikosongkan.');
    }

    /**
     * Buy now: add a single product variant to cart and redirect to checkout.
     */
    public function buyNow(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Store buy‑now context in session (separate from regular cart)
        $request->session()->put('checkout_source', 'buy_now');

$request->session()->put('buy_now', [
    'product_variant_id' => $validated['product_variant_id'],
    'quantity' => (int) $validated['quantity'],
    'token' => (string) Str::uuid(),
]);

return redirect()->route('checkout.index')
    ->with('success', 'Produk siap checkout via Beli Sekarang.');
        // Redirect directly to checkout page
        return redirect()->route('checkout.index')
            ->with('success', 'Produk siap checkout via Beli Sekarang.');
    }
}
