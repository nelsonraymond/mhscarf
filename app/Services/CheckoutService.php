<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
    protected CartService $cartService,
    protected InventoryService $inventoryService,
    protected ShippingService $shippingService,
    protected StockReservationService $stockReservationService,
    protected VoucherService $voucherService,
) {}

    /**
     * Prepare cart items and recalculate server-side totals.
     *
     * @return array{
     *     cart: Cart,
     *     items: Collection<int, CartItem>,
     *     subtotal: float,
     *     itemCount: int
     * }
     */
    public function getValidatedCartData(User $user): array
    {
        $cart = $this->cartService->getOrCreateCart($user, session()->getId());
        $cart->load(['items.variant.product.images']);

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang belanja Anda masih kosong.',
            ]);
        }

        $subtotal = 0.0;
        $itemCount = 0;

        foreach ($cart->items as $item) {
            $variant = $item->variant;
            $product = $variant?->product;

            if (! $variant || ! $variant->is_active || ! $product || ! $product->is_active) {
                throw ValidationException::withMessages([
                    'cart' => 'Produk pada keranjang tidak lagi tersedia atau sedang tidak aktif.',
                ]);
            }

            if ($variant->stock_qty <= 0) {
                throw ValidationException::withMessages([
                    'cart' => "Stok untuk produk {$product->name} ({$variant->name}) sedang habis.",
                ]);
            }

            if ($item->quantity > $variant->stock_qty) {
                throw ValidationException::withMessages([
                    'cart' => "Kuantitas untuk {$product->name} ({$variant->name}) melebihi stok yang tersedia (tersedia: {$variant->stock_qty}).",
                ]);
            }

            $currentPrice = $variant->finalPrice();
            $subtotal += ($currentPrice * $item->quantity);
            $itemCount += $item->quantity;
        }

        return [
            'cart' => $cart,
            'items' => $cart->items,
            'subtotal' => $subtotal,
            'itemCount' => $itemCount,
        ];
    }

    /**
 * Subtotal pesanan saat ini (Beli Sekarang atau keranjang), dihitung ulang di server.
 */
public function currentSubtotal(User $user): float
{
    if (session()->has('buy_now')) {
        $buyNow = session()->get('buy_now');
        $variant = ProductVariant::with('product')->findOrFail($buyNow['product_variant_id']);

        return (float) ($variant->finalPrice() * (int) $buyNow['quantity']);
    }

    return (float) $this->getValidatedCartData($user)['subtotal'];
}

    /**
     * Create an order from current customer's cart (or Buy Now session).
     *
     * @param array{
     *     address_id: int,
     *     shipping_method: string,
     *     customer_note?: ?string
     * } $data
     */
    public function placeOrder(User $user, array $data): Order
    {
        // 1. Verify that address belongs to user
        /** @var Address $address */
        $address = $user->addresses()->where('id', $data['address_id'])->first();
        if (! $address) {
            throw ValidationException::withMessages([
                'address_id' => 'Alamat pengiriman tidak valid atau bukan milik Anda.',
            ]);
        }

        // 2. Validate shipping method & cost server-side
        $shippingOption = $this->shippingService->getMethod($data['shipping_method']);
        $shippingCost = $shippingOption['cost'];

        return DB::transaction(function () use ($user, $address, $shippingOption, $shippingCost, $data) {
            $cart = null;

            if (session()->has('buy_now')) {
                // ----- Buy Now flow -----
                $buyNow = session()->get('buy_now');
                $quantity = (int) $buyNow['quantity'];

                // Lock the single variant for update
                $lockedVariant = ProductVariant::where('id', $buyNow['product_variant_id'])
                    ->with('product')
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedVariants = collect([$lockedVariant->id => $lockedVariant]);
                $cartItems = collect([
                    (object) [
                        'product_variant_id' => $lockedVariant->id,
                        'quantity' => $quantity,
                    ],
                ]);
            } else {
                // ----- Regular cart flow -----
                $cart = $this->cartService->getCart($user, session()->getId());

                if (! $cart) {
                    throw ValidationException::withMessages(['cart' => 'Keranjang belanja Anda kosong.']);
                }

                // Serialisasi checkout pada keranjang yang sama, lalu baca ulang item dengan locking read.
                // Request kedua menunggu di sini, lalu melihat keranjang yang sudah kosong.
                Cart::whereKey($cart->id)->lockForUpdate()->first();
                $cartItems = $cart->items()->lockForUpdate()->get();

                if ($cartItems->isEmpty()) {
                    throw ValidationException::withMessages(['cart' => 'Keranjang belanja Anda kosong.']);
                }

                $lockedVariants = ProductVariant::whereIn('id', $cartItems->pluck('product_variant_id')->unique()->all())
                    ->orderBy('id')
                    ->with('product')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
            }

            // Validate, reserve and deduct stock
            $orderItemsData = $this->stockReservationService->reserveAndDeduct(
                lockedVariants: $lockedVariants,
                cartItems: $cartItems,
                userId: $user->id,
                shippingCost: $shippingCost
            );

            $orderSubtotal = array_sum(array_column($orderItemsData, 'subtotal'));

// Voucher: dikunci dan divalidasi ulang di dalam transaksi ini
$voucherResult = null;
if (! empty($data['voucher_code'])) {
    $voucherResult = $this->voucherService->lockAndEvaluate(
        $data['voucher_code'],
        $user,
        (float) $orderSubtotal,
        (float) $shippingCost
    );
}
$discount = $voucherResult['discount'] ?? 0.0;
$grandTotal = $orderSubtotal + $shippingCost - $discount;

// Create Order
$order = Order::create([
    'order_number' => $this->generateUniqueOrderNumber(),
    'user_id' => $user->id,
    'status' => 'pending',
    'payment_status' => 'pending',
    'subtotal' => $orderSubtotal,
    'discount_amount' => $discount,
    'shipping_cost' => $shippingCost,
    'grand_total' => $grandTotal,
    'voucher_code' => $voucherResult['voucher']->code ?? null,
    'voucher_discount' => $discount,
    'shipping_recipient_name' => $address->recipient_name,
    'shipping_phone' => $address->phone,
    'shipping_address' => $address->fullAddress(),
    'shipping_city' => $address->city,
    'shipping_province' => $address->province,
    'shipping_postal_code' => $address->postal_code,
    'customer_note' => $data['customer_note'] ?? null,
    'placed_at' => now(),
]);

if ($voucherResult) {
    $this->voucherService->recordUsage($voucherResult['voucher'], $user, $order, $discount);
}

            // Create immutable OrderItem snapshots and link each stock reservation to its item.
            // Tanpa tautan order_item_id, konfirmasi bayar, pembatalan, dan sweeper tidak menemukan reservasinya.
            foreach ($orderItemsData as $itemData) {
                $orderItem = $order->items()->create([
                    'product_id' => $itemData['variant']->product_id,
                    'product_variant_id' => $itemData['variant']->id,
                    'product_name' => $itemData['variant']->product->name,
                    'variant_name' => $itemData['variant']->name,
                    'sku' => $itemData['variant']->sku,
                    'unit_price' => $itemData['unit_price'],
                    'quantity' => $itemData['quantity'],
                    'subtotal' => $itemData['subtotal'],
                ]);

                $itemData['reservation']->forceFill(['order_item_id' => $orderItem->id])->save();
            }

            // Create Shipment record
            Shipment::create([
                'order_id' => $order->id,
                'courier' => $shippingOption['courier'],
                'service' => $shippingOption['service'],
                'tracking_number' => null,
                'shipping_cost' => $shippingCost,
                'status' => 'pending',
            ]);

            // Clear the user's cart only for regular flow
            if ($cart) {
                $this->cartService->clearCart($cart);
            }

            // Remove buy_now session flag after successful order
            if (session()->has('buy_now')) {
                session()->forget('buy_now');
            }

            return $order;
        });
    }

    /**
     * Generate unique order number with prefix and timestamp.
     */
    protected function generateUniqueOrderNumber(): string
    {
        do {
            $number = 'ORD-'.date('Ymd').'-'.strtoupper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}