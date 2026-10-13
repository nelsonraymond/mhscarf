<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\StockReservation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockReservationService
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    /**
     * Validate cart items, lock stock, create reservations and deduct stock.
     *
     * @param  Collection|ProductVariant[]  $lockedVariants  Variants locked for update, keyed by id.
     * @param  Collection|CartItem[]  $cartItems
     * @param  float  $shippingCost  (unused here but kept for signature compatibility)
     * @return array Array of order item data used by CheckoutService.
     */
    public function reserveAndDeduct(
        Collection $lockedVariants,
        Collection $cartItems,
        int $userId,
        float $shippingCost = 0.0
    ): array {
        // Ensure deterministic lock order
        $variantIds = $lockedVariants->keys()->sort()->values();
        // Re‑order the collection to follow the sorted IDs
        $orderedLocked = $variantIds->map(fn ($id) => $lockedVariants->get($id));

        $orderItemsData = [];

        DB::transaction(function () use ($orderedLocked, $cartItems, $userId, &$orderItemsData) {
            foreach ($cartItems as $cartItem) {
                $variant = $orderedLocked->firstWhere('id', $cartItem->product_variant_id);

                if (! $variant || ! $variant->is_active || ! $variant->product?->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Salah satu produk yang dipilih sudah tidak tersedia.',
                    ]);
                }

                if ($cartItem->quantity > $variant->stock_qty) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok tidak mencukupi untuk {$variant->product->name} ({$variant->name}). Sisa stok: {$variant->stock_qty}.",
                    ]);
                }

                $unitPrice = $variant->finalPrice();
                $subtotal = $unitPrice * $cartItem->quantity;

                // Create reservation (status pending, expires in 15 minutes)
                StockReservation::create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $cartItem->quantity,
                    'status' => StockReservation::STATUS_PENDING,
                    'expires_at' => Carbon::now()->addMinutes(15),
                ]);

                // Deduct stock via InventoryService (records movement)
                $this->inventoryService->recordMovement(
                    variant: $variant,
                    quantity: $cartItem->quantity,
                    type: 'out',
                    userId: $userId,
                    note: 'Reservasi stok selama checkout',
                    referenceType: null,
                    referenceId: null,
                );

                $orderItemsData[] = [
                    'variant' => $variant,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }
        });

        return $orderItemsData;
    }

    /**
     * Release a reservation (idempotent).
     * If the reservation is still pending, stock is returned and status set to released.
     */
    public function releaseReservation(StockReservation $reservation): void
    {
        // Refresh the reservation to get the latest status from the database.
        $reservation = $reservation->fresh();

        if ($reservation->status !== StockReservation::STATUS_PENDING) {
            // Already released/cancelled – nothing to do.
            return;
        }

        DB::transaction(function () use ($reservation) {
            $variant = $reservation->variant;
            if ($variant) {
                $this->inventoryService->recordMovement(
                    variant: $variant,
                    quantity: $reservation->quantity,
                    type: 'in',
                    userId: null,
                    note: 'Release reservation expiry',
                    referenceType: null,
                    referenceId: null,
                );
            }
            $reservation->update([
                'status' => StockReservation::STATUS_RELEASED,
            ]);
        });
    }
}
