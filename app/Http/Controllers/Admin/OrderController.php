<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidOrderStateTransitionException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\ShipmentTrackingService;
use App\Services\TransactionalEmailService;
use App\Exceptions\ShippingProviderException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected ShipmentTrackingService $shipmentTrackingService,
        protected TransactionalEmailService $transactionalEmailService,
    ) {}

    /**
     * Display a paginated list of all customer orders for admin.
     */
    public function index(Request $request): View
    {
        $request->validate([
            'status' => ['nullable', 'in:pending,confirmed,processing,packed,shipped,delivered,completed,cancelled'],
            'payment_status' => ['nullable', 'in:pending,paid,failed,expired,refunded'],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $query = Order::query()->with(['user', 'items', 'payments', 'shipment']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->boolean('needs_refund')) {
    $query->whereHas('payments', fn ($q) => $q->where('metadata->needs_refund', true));
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
        $order->load(['user', 'items.variant.product.images', 'payments', 'shipment', 'statusHistories.actor']);

        // Determine allowed next statuses according to state machine
        $allowedNextStatuses = OrderService::ALLOWED_TRANSITIONS[$order->status] ?? [];
        $trackingSnapshot = $order->shipment
            ? $this->shipmentTrackingService->cachedSnapshot($order->shipment)
            : null;

        return view('admin.orders.show', compact('order', 'allowedNextStatuses', 'trackingSnapshot'));
    }

    /** Refresh tracking through a throttled, admin-only action. */
    public function refreshTracking(Order $order): RedirectResponse
    {
        $shipment = $order->shipment;
        if (! $shipment) {
            return back()->with('error', 'Informasi pengiriman belum tersedia.');
        }

        try {
            $this->shipmentTrackingService->refresh($shipment);

            return back()->with('success', 'Informasi pelacakan berhasil diperbarui.');
        } catch (ShippingProviderException|\InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }
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
            $previousStatus = null;
            $updatedOrder = DB::transaction(function () use ($order, $validated, &$previousStatus) {
                $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                $previousStatus = $locked->status;

                return $this->orderService->transitionStatus(
                    order: $locked,
                    newStatus: $validated['status'],
                    reason: $validated['note'] ?? 'Diperbarui oleh admin',
                    actor: Auth::user()
                );
            });

            if ($previousStatus !== 'shipped' && $updatedOrder->status === 'shipped') {
                $shipment = $updatedOrder->shipment;
                $this->transactionalEmailService->shipmentDispatched($updatedOrder->fresh(['items', 'shipment']), $shipment);
            }

            return back()->with('success', "Status pesanan #{$order->order_number} berhasil diperbarui.");
        } catch (InvalidOrderStateTransitionException|\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update shipment details for an order.
     */
    public function updateShipment(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'courier' => ['required', 'string', 'max:80'],
            'courier_code' => ['nullable', 'string', Rule::in(\App\Services\ShippingService::availableCouriers())],
            'service' => ['required', 'string', 'max:80'],
            'tracking_number' => ['nullable', 'string', 'max:120'],
        ]);

        $saved = DB::transaction(function () use ($order, $validated): bool {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->payment_status !== 'paid'
                || ! in_array($locked->status, ['confirmed', 'processing', 'packed'], true)) {
                return false;
            }

            $locked->shipment()->updateOrCreate([], $validated + ['status' => 'pending']);

            return true;
        });

        if (! $saved) {
            return back()->with('error', 'Informasi pengiriman hanya dapat diubah setelah pembayaran dikonfirmasi dan sebelum pesanan dikirim.');
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

    /**
 * Tandai bahwa refund sudah dilakukan manual (di dashboard Midtrans / transfer langsung).
 */
public function markRefunded(Order $order): RedirectResponse
{
    $marked = DB::transaction(function () use ($order) {
        $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
        $count = 0;

        $payments = $locked->payments()->where('metadata->needs_refund', true)->lockForUpdate()->get();

        foreach ($payments as $payment) {
            $payment->update([
                'status' => 'refunded',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'needs_refund' => false,
                    'refunded_marked_at' => now()->toIso8601String(),
                    'refunded_marked_by' => Auth::id(),
                ]),
            ]);
            $count++;
        }

        if ($count > 0) {
            $locked->update(['payment_status' => 'refunded']);
        }

        return $count;
    });

    if ($marked === 0) {
        return back()->with('error', 'Tidak ada pembayaran yang perlu direfund pada pesanan ini.');
    }

    return back()->with('success', "Pesanan #{$order->order_number} ditandai sudah direfund.");
}
}
