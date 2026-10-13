<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use App\Services\ShippingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected ShippingService $shippingService,
        protected PaymentService $paymentService,
    ) {}

    /**
     * Display the checkout page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get();
        $shippingMethods = $this->shippingService->getAvailableMethods();
        $defaultShipping = 'regular';
        $shippingCost = $shippingMethods[$defaultShipping]['cost'];

        // Buy‑Now flow
        if ($request->session()->has('buy_now')) {
            $buyNow = $request->session()->get('buy_now');
            $variant = ProductVariant::with('product')->findOrFail($buyNow['product_variant_id']);
            $quantity = (int) $buyNow['quantity'];

            // Validate variant and stock
            if (! $variant->is_active || ! $variant->product || ! $variant->product->is_active) {
                return redirect()->route('cart.index')->with('warning', 'Produk tidak tersedia.');
            }
            if ($variant->stock_qty <= 0) {
                return redirect()->route('cart.index')->with('warning', "Stok untuk {$variant->product->name} ({$variant->name}) habis.");
            }
            if ($quantity > $variant->stock_qty) {
                return redirect()->route('cart.index')->with('warning', "Kuantitas melebihi stok (tersedia: {$variant->stock_qty}).");
            }

            $subtotal = $variant->finalPrice() * $quantity;
            $itemCount = $quantity;
            $cartItems = collect([
                (object) [
                    'variant' => $variant,
                    'quantity' => $quantity,
                ],
            ]);
            $cart = null;
        } else {
            // Regular cart flow
            try {
                $cartData = $this->checkoutService->getValidatedCartData($user);
            } catch (ValidationException $e) {
                $message = $e->validator->errors()->first('cart') ?: 'Keranjang belanja Anda kosong.';

                return redirect()->route('cart.index')->with('warning', $message);
            }
            $cart = $cartData['cart'];
            $cartItems = $cartData['items'];
            $subtotal = $cartData['subtotal'];
            $itemCount = $cartData['itemCount'];
        }

        $grandTotal = $subtotal + $shippingCost;

        return view('checkout.index', [
            'cart' => $cart,
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'itemCount' => $itemCount,
            'addresses' => $addresses,
            'defaultAddress' => $addresses->firstWhere('is_default', true) ?? $addresses->first(),
            'shippingMethods' => $shippingMethods,
            'defaultShipping' => $defaultShipping,
            'shippingCost' => $shippingCost,
            'grandTotal' => $grandTotal,
        ]);
    }

    /**
     * Handle order submission and confirmation.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'address_id' => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where('user_id', $user->id),
            ],
            'shipping_method' => [
                'required',
                'string',
                Rule::in(array_keys($this->shippingService->getAvailableMethods())),
            ],
            'customer_note' => ['nullable', 'string', 'max:500'],
        ], [
            'address_id.required' => 'Silakan pilih atau tambahkan alamat pengiriman terlebih dahulu.',
            'address_id.exists' => 'Alamat pengiriman yang dipilih tidak valid atau bukan milik Anda.',
            'shipping_method.required' => 'Silakan pilih metode pengiriman.',
            'shipping_method.in' => 'Metode pengiriman yang dipilih tidak valid.',
        ]);

        try {
            // Store order in DB transaction (no external API calls)
            $order = null;
            DB::transaction(function () use ($user, $validated, &$order) {
                $order = $this->checkoutService->placeOrder($user, $validated);
            });

            // Initialize payment after order is persisted (may throw RuntimeException)
            $payment = $this->paymentService->initializePayment($order);

            // Return response based on request type
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'snap_token' => $payment->metadata['snap_token'] ?? null,
                    'client_key' => $payment->metadata['client_key'] ?? null,
                    'redirect_url' => $payment->metadata['redirect_url'] ?? null,
                ]);
            }

            // Fallback for non‑AJAX submissions
            return redirect()->route('payments.show', $order)
                ->with('success', "Pesanan #{$order->order_number} berhasil dibuat!");
        } catch (\RuntimeException $e) {
            // Return error details for AJAX or fallback to redirect with error flash
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return back()->with('error', $e->getMessage());
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['errors' => $e->validator->errors()], 422);
            }

            return back()->withInput()->withErrors($e->validator);
        }
    }
}
