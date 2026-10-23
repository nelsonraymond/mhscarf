<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Exceptions\ShippingProviderException;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use App\Services\ShippingService;
use App\Services\TransactionalEmailService;
use App\Services\VoucherService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
    protected CheckoutService $checkoutService,
    protected ShippingService $shippingService,
    protected PaymentService $paymentService,
    protected VoucherService $voucherService,
    protected TransactionalEmailService $transactionalEmailService,
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
        $shippingCost = 0;
        $shippingWeight = null;
        $shippingWeightError = null;
        try {
            $shippingWeight = $this->checkoutService->currentShippingWeight($user);
        } catch (ValidationException $exception) {
            $shippingWeightError = $exception->errors()['shipping_quote_token'][0]
                ?? 'Berat varian produk belum tersedia.';
        }

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

        $checkoutToken = $request->session()->has('buy_now')
    ? ($request->session()->get('buy_now')['token'] ?? (string) Str::uuid())
    : (string) Str::uuid();

        $grandTotal = $subtotal + $shippingCost;

        return view('checkout.index', [
            'cart' => $cart,
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'itemCount' => $itemCount,
            'addresses' => $addresses,
            'defaultAddress' => $addresses->firstWhere('is_default', true) ?? $addresses->first(),
            'shippingCost' => $shippingCost,
            'shippingWeight' => $shippingWeight,
            'shippingWeightError' => $shippingWeightError,
            'shippingCouriers' => ShippingService::availableCouriers(),
            'grandTotal' => $grandTotal,
            'checkoutToken' => $checkoutToken,
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
        'voucher_code' => ['nullable', 'string', 'max:50'],
        'shipping_quote_token' => ['required', 'string', 'max:8192'],
        'customer_note' => ['nullable', 'string', 'max:500'],
        'checkout_token' => ['nullable', 'string', 'max:64'],
    ], [
        'address_id.required' => 'Silakan pilih atau tambahkan alamat pengiriman terlebih dahulu.',
        'address_id.exists' => 'Alamat pengiriman yang dipilih tidak valid atau bukan milik Anda.',
        'shipping_quote_token.required' => 'Pilih tujuan dan layanan ongkir sebelum checkout.',
    ]);

    $wantsJson = $request->expectsJson() || $request->ajax();
    $key = ! empty($validated['checkout_token'])
        ? 'checkout-idem:'.$user->id.':'.$validated['checkout_token']
        : null;

    // Cache may be left at "processing" after a worker timeout. The order row is
    // the durable idempotency record, so recover a committed order before claiming cache.
    if (! empty($validated['checkout_token'])) {
        $existingOrder = Order::query()
            ->where('user_id', $user->id)
            ->where('checkout_token', $validated['checkout_token'])
            ->first();

        if ($existingOrder) {
            if ($key) {
                Cache::put($key, $existingOrder->id, now()->addHour());
            }

            return $this->paymentResponse($request, $existingOrder, $wantsJson);
        }
    }

    // Klaim token secara atomik. Yang kalah balapan tidak boleh membuat pesanan baru.
    // Keep the in-flight lease only slightly beyond the worker's normal request
    // timeout; a crashed request must not block a safe retry for ten minutes.
    if ($key && ! Cache::add($key, 'processing', now()->addMinutes(2))) {
        $existing = Cache::get($key);
        $order = ctype_digit((string) $existing)
            ? Order::where('user_id', $user->id)->find((int) $existing)
            : null;

        if (! $order) {
            $message = 'Pesanan Anda sedang diproses. Mohon tunggu sebentar.';

            return $wantsJson
                ? response()->json(['error' => $message], 409)
                : back()->with('error', $message);
        }

        // Pesanan sudah ada: kembalikan pesanan yang sama (dan coba siapkan pembayaran lagi)
        return $this->paymentResponse($request, $order, $wantsJson);
    }

    $order = null;

    try {
        $address = $user->addresses()->findOrFail($validated['address_id']);
        $weightGram = $this->checkoutService->currentShippingWeight($user);
        $validated['shipping_quote'] = $this->shippingService->revalidateQuote(
            $validated['shipping_quote_token'],
            $user,
            $address,
            $weightGram,
            $this->checkoutService->currentShippingFingerprint($user),
        );

        $order = $this->checkoutService->placeOrder($user, $validated);

        // Persist the result immediately after the transaction commits.
        if ($key) {
            Cache::put($key, $order->id, now()->addHour());
        }

        // placeOrder() has committed its transaction before returning.
        $this->transactionalEmailService->orderPlaced($order);

        return $this->paymentResponse($request, $order, $wantsJson);
    } catch (ValidationException $e) {
        if ($wantsJson) {
            return response()->json(['errors' => $e->validator->errors()], 422);
        }

        return back()->withInput()->withErrors($e->validator);
    } catch (ShippingProviderException $e) {
        return $wantsJson
            ? response()->json(['error' => $e->getMessage()], 503)
            : back()->withInput()->withErrors(['shipping_quote_token' => $e->getMessage()]);
    } finally {
        // Pesanan gagal dibuat: lepas token supaya user bisa mencoba lagi
        if ($key && $order === null) {
            Cache::forget($key);
        }
    }
}

/**
 * Siapkan pembayaran untuk pesanan dan susun respons (JSON untuk Snap, redirect untuk non-AJAX).
 */
protected function paymentResponse(Request $request, Order $order, bool $wantsJson)
{
    try {
        $payment = $this->paymentService->initializePayment($order);
    } catch (\RuntimeException|\InvalidArgumentException $e) {
        if ($e instanceof \RuntimeException) {
            Log::warning('Checkout payment initialization failed', [
                'order_id' => $order->id,
                'exception' => $e::class,
            ]);
        }

        $message = $e instanceof \InvalidArgumentException
            ? $e->getMessage()
            : 'Pesanan sudah dibuat, tetapi pembayaran belum dapat disiapkan. Buka halaman pesanan untuk melanjutkan.';

        return $wantsJson
            ? response()->json(['error' => $message, 'order_id' => $order->id], $e instanceof \RuntimeException ? 500 : 409)
            : redirect()->route('orders.show', $order)->with('error', $message);
    }

    if ($wantsJson) {
        return response()->json([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'snap_token' => $payment->metadata['snap_token'] ?? null,
            'client_key' => $payment->metadata['client_key'] ?? null,
            'redirect_url' => $payment->metadata['redirect_url'] ?? null,
        ]);
    }

    return redirect()->route('payments.show', $order)
        ->with('success', "Pesanan #{$order->order_number} berhasil dibuat!");
}

public function voucher(Request $request): JsonResponse
{
    $data = $request->validate([
        'voucher_code' => ['required', 'string', 'max:50'],
        'address_id' => ['required', 'integer', Rule::exists('addresses', 'id')->where('user_id', $request->user()->id)],
        'shipping_quote_token' => ['required', 'string', 'max:8192'],
    ]);

    $user = $request->user();
    $address = $user->addresses()->findOrFail($data['address_id']);
    $subtotal = $this->checkoutService->currentSubtotal($user);
    $weightGram = $this->checkoutService->currentShippingWeight($user);
    $quote = $this->shippingService->resolveQuote(
        $data['shipping_quote_token'],
        $user,
        $address,
        $weightGram,
        $this->checkoutService->currentShippingFingerprint($user),
    );
    $shipping = (float) $quote['cost'];

    $result = $this->voucherService->preview($data['voucher_code'], $user, $subtotal, $shipping);

    return response()->json([
        'code' => $result['voucher']->code,
        'discount' => $result['discount'],
        'type' => $result['voucher']->type,
        'total' => $subtotal + $shipping - $result['discount'],
    ]);
}
}
