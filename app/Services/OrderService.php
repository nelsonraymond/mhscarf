<?php

namespace App\Services;

use App\Exceptions\InvalidOrderStateTransitionException;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\StockReservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    /**
     * Allowed status transitions for an Order.
     */
    public const ALLOWED_TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['packed', 'cancelled'],
        'packed' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    /**
     * Check if status transition is permissible according to state machine.
     */
    public function canTransition(string $from, string $to): bool
    {
        $allowed = self::ALLOWED_TRANSITIONS[$from] ?? [];

        return in_array($to, $allowed, true);
    }

    /**
     * Transition order to a new state with validation.
     *
     * @throws InvalidOrderStateTransitionException
     */
    public function transitionStatus(Order $order, string $newStatus, ?string $reason = null, ?User $actor = null): Order
    {
        if ($order->status === $newStatus) {
            return $order;
        }

        if (! $this->canTransition($order->status, $newStatus)) {
            throw new InvalidOrderStateTransitionException($order->status, $newStatus);
        }

        if ($newStatus === 'cancelled') {
            return $this->cancelOrder($order, $reason, $actor);
        }

        $order->update(['status' => $newStatus]);

        return $order->fresh();
    }

    /**
     * Cancel an order and restore inventory atomically and idempotently.
     *
     * Ini satu-satunya jalur pengembalian stok untuk order. Baris order dikunci, sehingga
     * pembatalan ganda atau balapan dengan webhook pembayaran / sweeper diproses satu per satu.
     */
    public function cancelOrder(Order $order, ?string $reason = null, ?User $actor = null): Order
    {
        $cancellableStatuses = ['pending', 'confirmed', 'processing', 'packed'];

        $cancelled = DB::transaction(function () use ($order, $reason, $actor, $cancellableStatuses) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            // 1. Idempotency: sudah dibatalkan -> jangan kembalikan stok dua kali
            if ($locked->status === 'cancelled') {
                return $locked;
            }

            // 2. Validate cancellable states (cannot cancel once shipped, delivered, or completed)
            if (! in_array($locked->status, $cancellableStatuses, true)) {
                throw new InvalidArgumentException("Pesanan dengan status '{$locked->statusLabel()}' tidak dapat dibatalkan.");
            }

            // Restore product variant stock for each ordered item
            $locked->loadMissing('items.variant');

            foreach ($locked->items as $item) {
                $variant = $item->variant;
                if (! $variant) {
                    continue;
                }

                // Guard against duplicate stock restoration
                $alreadyReturned = InventoryMovement::where('reference_type', Order::class)
                    ->where('reference_id', $locked->id)
                    ->where('product_variant_id', $variant->id)
                    ->where('type', 'return')
                    ->exists();

                if (! $alreadyReturned) {
                    $note = "Pengembalian stok pembatalan pesanan #{$locked->order_number}";
                    if ($reason) {
                        $note .= " ({$reason})";
                    }

                    $this->inventoryService->recordMovement(
                        variant: $variant,
                        quantity: $item->quantity,
                        type: 'return',
                        userId: $actor?->id,
                        note: $note,
                        referenceType: Order::class,
                        referenceId: $locked->id,
                    );
                }
            }

            // Reservasi milik order ini sudah tidak menahan stok (stok dikembalikan di atas)
            StockReservation::query()
                ->whereIn('order_item_id', $locked->items->pluck('id'))
                ->whereIn('status', [StockReservation::STATUS_PENDING, StockReservation::STATUS_RESERVED])
                ->update(['status' => StockReservation::STATUS_RELEASED]);

            $locked->update([
                'status' => 'cancelled',
            ]);

            return $locked->fresh();
        });

        // Sinkronkan instance milik pemanggil supaya $order->status tidak basi
        $order->forceFill($cancelled->getAttributes())->syncOriginal();

        return $cancelled;
    }
}