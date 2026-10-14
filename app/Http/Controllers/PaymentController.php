<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
    ) {}

    /**
     * Display customer payment page.
     */
    public function show(Order $order): View|RedirectResponse
    {
        // Enforce customer ownership authorization
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses ke pembayaran pesanan ini.');
        }

        try {
            $payment = $this->paymentService->initializePayment($order);
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            return redirect()->route('orders.show', $order)
                ->with('error', $e->getMessage());
        }

        $order->refresh()->load(['items.variant.product', 'shipment']);

        return view('payments.show', compact('order', 'payment'));
    }

    /**
     * Status ringan untuk polling dari halaman pembayaran.
     */
  

    public function status(Request $request, Order $order): JsonResponse
{
    abort_unless($order->user_id === $request->user()->id, 403);

    // Tanya Midtrans langsung, jadi tetap jalan tanpa webhook/ngrok
    $order = $this->paymentService->syncFromGateway($order);

    return response()->json([
        'payment_status' => $order->payment_status,
        'order_status'   => $order->status,
        'paid'           => $order->payment_status === 'paid',
    ]);
}

    /**
     * Handle webhook callback from payment gateway.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('X-Signature-Key');

        $result = $this->paymentService->handleWebhook($payload, $signature);

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook berhasil diproses.',
            'data' => [
                'order_number' => $result['order']->order_number,
                'order_status' => $result['order']->status,
                'payment_status' => $result['payment']->status,
            ],
        ]);
    }
}