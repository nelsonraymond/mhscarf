<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * Get or create the cart for an authenticated user or guest session.
     */
    public function getOrCreateCart(?User $user, string $sessionId, ?int $cartId = null): Cart
    {
        if ($user) {
    try {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    } catch (\Illuminate\Database\UniqueConstraintViolationException) {
        return Cart::where('user_id', $user->id)->firstOrFail();
    }
}

        if ($cartId) {
            $cart = Cart::where('id', $cartId)->whereNull('user_id')->first();
            if ($cart) {
                return $cart;
            }
        }

        return Cart::firstOrCreate(
            ['session_id' => $sessionId]
        );
    }

    /**
     * Find existing cart without automatically creating one.
     */
    public function getCart(?User $user, ?string $sessionId, ?int $cartId = null): ?Cart
    {
        if ($user) {
            return Cart::where('user_id', $user->id)->first();
        }

        if ($cartId) {
            $cart = Cart::where('id', $cartId)->whereNull('user_id')->first();
            if ($cart) {
                return $cart;
            }
        }

        if ($sessionId) {
            return Cart::where('session_id', $sessionId)->first();
        }

        return null;
    }

    /**
     * Add a product variant to the cart.
     */
    public function addItem(Cart $cart, ProductVariant $variant, int $quantity = 1): CartItem
    {
        // 1. Validate that product and variant are active
        $product = $variant->product;
        if (! $variant->is_active || ! $product || ! $product->is_active) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'Produk atau varian yang dipilih sedang tidak aktif atau tidak tersedia.',
            ]);
        }

        // 2. Validate stock availability
        if ($variant->stock_qty <= 0) {
            throw ValidationException::withMessages([
                'product_variant_id' => "Stok untuk varian {$variant->name} saat ini sedang habis.",
            ]);
        }

        // 3. Recalculate server-side price (never trust frontend input)
        $unitPrice = $variant->finalPrice();

        return DB::transaction(function () use ($cart, $variant, $quantity, $unitPrice) {
            $existingItem = $cart->items()->where('product_variant_id', $variant->id)->first();

            $newQuantity = $existingItem ? ($existingItem->quantity + $quantity) : $quantity;

            if ($newQuantity > $variant->stock_qty) {
                if ($existingItem && $existingItem->quantity >= $variant->stock_qty) {
                    throw ValidationException::withMessages([
                        'quantity' => "Anda sudah memiliki kuantitas maksimal ({$variant->stock_qty} pcs) varian ini di keranjang.",
                    ]);
                }

                throw ValidationException::withMessages([
                    'quantity' => "Kuantitas melebihi stok yang tersedia ({$variant->stock_qty} pcs).",
                ]);
            }

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $newQuantity,
                    'unit_price' => $unitPrice,
                ]);

                return $existingItem->fresh();
            }

            return $cart->items()->create([
                'product_variant_id' => $variant->id,
                'quantity' => $newQuantity,
                'unit_price' => $unitPrice,
            ]);
        });
    }

    /**
     * Update cart item quantity.
     */
    public function updateItemQuantity(CartItem $item, int $quantity): CartItem
    {
        if ($quantity <= 0) {
            $item->delete();

            return $item;
        }

        $variant = $item->variant;
        if (! $variant || ! $variant->is_active || ! $variant->product?->is_active) {
            $item->delete();
            throw ValidationException::withMessages([
                'quantity' => 'Produk tidak lagi tersedia dan telah dihapus dari keranjang Anda.',
            ]);
        }

        if ($quantity > $variant->stock_qty) {
            throw ValidationException::withMessages([
                'quantity' => "Kuantitas melebihi stok yang tersedia ({$variant->stock_qty} pcs).",
            ]);
        }

        $item->update([
            'quantity' => $quantity,
            'unit_price' => $variant->finalPrice(),
        ]);

        return $item->fresh();
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    /**
     * Clear all items in cart.
     */
    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }

    /**
     * Merge guest session cart into authenticated user cart.
     */
    public function mergeGuestCart(string $sessionId, User $user, ?int $guestCartId = null): void
    {
        $guestCart = null;

        if ($guestCartId) {
            $guestCart = Cart::where('id', $guestCartId)
                ->whereNull('user_id')
                ->with('items.variant.product')
                ->first();
        }

        if (! $guestCart) {
            $guestCart = Cart::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->with('items.variant.product')
                ->first();
        }

        if (! $guestCart || $guestCart->items->isEmpty()) {
            if ($guestCart) {
                $guestCart->delete();
            }

            return;
        }

        DB::transaction(function () use ($guestCart, $user) {
            $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

            foreach ($guestCart->items as $guestItem) {
                $variant = $guestItem->variant;

                // Remove/skip invalid or out-of-stock items safely
                if (! $variant || ! $variant->is_active || ! $variant->product?->is_active || $variant->stock_qty <= 0) {
                    continue;
                }

                $existingUserItem = $userCart->items()
                    ->where('product_variant_id', $variant->id)
                    ->first();

                if ($existingUserItem) {
                    $mergedQuantity = min($existingUserItem->quantity + $guestItem->quantity, $variant->stock_qty);
                    $existingUserItem->update([
                        'quantity' => $mergedQuantity,
                        'unit_price' => $variant->finalPrice(),
                    ]);
                } else {
                    $itemQty = min($guestItem->quantity, $variant->stock_qty);
                    if ($itemQty > 0) {
                        $userCart->items()->create([
                            'product_variant_id' => $variant->id,
                            'quantity' => $itemQty,
                            'unit_price' => $variant->finalPrice(),
                        ]);
                    }
                }
            }

            $guestCart->items()->delete();
            $guestCart->delete();
        });
    }

    /**
     * Check if a user or session owns the given cart item.
     */
    public function authorizeItem(CartItem $item, ?User $user, string $sessionId, ?int $sessionCartId = null): bool
    {
        if ($user) {
            return $item->cart?->user_id === $user->id;
        }

        if ($sessionCartId && $item->cart_id === $sessionCartId) {
            return true;
        }

        return $item->cart?->session_id === $sessionId;
    }
}
