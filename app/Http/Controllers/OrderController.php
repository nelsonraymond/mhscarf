<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\ShipmentTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
    ) {}

    /**
     * Display customer order history.
     */
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()
    ->with(['items.variant.product.images', 'items.review', 'shipment', 'payments'])
    ->latest('placed_at')
    ->latest('id')
    ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Display single order details with tracking timeline.
     */
    public function show(Order $order, ShipmentTrackingService $trackingService): View
    {
        // Enforce authorization: customer can only view their own order
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat pesanan ini.');
        }

        $order->load(['items.variant.product.images', 'items.review', 'shipment', 'payments', 'user']);
        $trackingSnapshot = $order->shipment
            ? $trackingService->cachedSnapshot($order->shipment)
            : null;

        return view('orders.show', compact('order', 'trackingSnapshot'));
    }

    /**
     * Cancel customer's own order if still pending.
     */
    public function cancel(Order $order, Request $request): RedirectResponse
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk membatalkan pesanan ini.');
        }

        try {
            $this->orderService->cancelOrder($order, 'Dibatalkan oleh pelanggan', Auth::user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pesanan berhasil dibatalkan dan stok produk telah dikembalikan.');
    }
}
