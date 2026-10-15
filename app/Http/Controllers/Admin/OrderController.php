<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidOrderStateTransitionException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
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
     * Display a paginated list of all customer orders for admin.
     */
    public function index(Request $request): View
    {
        $query = Order::query()->with(['user', 'items', 'payments', 'shipment']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('shipping_recipient_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->latest('placed_at')->latest('id')->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display full order details with status transition controls.
     */
    public function show(Order $order): View
    {
        $order->load(['user', 'items.variant.product.images', 'payments', 'shipment']);

        // Determine allowed next statuses according to state machine
        $allowedNextStatuses = OrderService::ALLOWED_TRANSITIONS[$order->status] ?? [];

        return view('admin.orders.show', compact('order', 'allowedNextStatuses'));
    }

    /**
     * Manually transition order status according to state machine.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,processing,packed,shipped,delivered,completed,cancelled'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->orderService->transitionStatus(
                order: $order,
                newStatus: $validated['status'],
                reason: $validated['note'] ?? 'Diperbarui oleh admin',
                actor: Auth::user()
            );

            return back()->with('success', "Status pesanan #{$order->order_number} berhasil diperbarui.");
        } catch (InvalidOrderStateTransitionException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update shipment details for an order.
     */
    public function updateShipment(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'courier' => ['nullable', 'string', 'max:255'],
            'service' => ['nullable', 'string', 'max:255'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'in:pending,picked_up,in_transit,shipped,delivered,returned,cancelled'],
        ]);

        $shipment = $order->shipment;

        if (array_key_exists('status', $validated) && $validated['status'] === null) {
            unset($validated['status']);
        }

        if (($validated['status'] ?? null) === 'shipped' && empty($shipment?->shipped_at)) {
            $validated['shipped_at'] = now();
        }

        if (($validated['status'] ?? null) === 'delivered' && empty($shipment?->delivered_at)) {
            $validated['delivered_at'] = now();
        }

        // Ensure shipment exists; create if missing
        if ($shipment) {
            $shipment->update($validated);
        } else {
            if (empty($validated['courier'])) {
                $validated['courier'] = 'MH Scarf Express Delivery';
            }
            if (empty($validated['status'])) {
                $validated['status'] = 'pending';
            }
            $order->shipment()->create($validated);
        }

        return back()->with('success', "Detail pengiriman untuk pesanan #{$order->order_number} berhasil diperbarui.");
    }

    /**
     * Cancel an order from admin panel and restore stock.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $reason = $validated['reason'] ?? 'Dibatalkan oleh administrator';

        try {
            $this->orderService->cancelOrder($order, $reason, Auth::user());

            return back()->with('success', "Pesanan #{$order->order_number} berhasil dibatalkan dan stok produk telah dikembalikan.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
