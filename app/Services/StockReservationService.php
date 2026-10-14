<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\StockReservation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class StockReservationService
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    /**
     * Lama stok ditahan selama menunggu pembayaran (menit).
     */
    public function reservationMinutes(): int
    {
        return max(1, (int) config('midtrans.expiry_minutes', 15));
    }

    /**
     * Validate cart items, lock stock, create reservations and deduct stock.
     *
     * @param  Collection|ProductVariant[]  $lockedVariants  Variants locked for update, keyed by id.
     * @param  Collection|CartItem[]  $cartItems
     * @param  float  $shippingCost  (unused here but kept for signature compatibility)
     * @return array<int, array{variant: ProductVariant, quantity: int, unit_price: float, subtotal: float, reservation: StockReservation}>
     *               Data item order yang dipakai CheckoutService. 'reservation' dipakai untuk
     *               menghubungkan reservasi ke order_item setelah item dibuat.
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

                // Create reservation (status pending, expires in N minutes)
                $reservation = StockReservation::create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $cartItem->quantity,
                    'status' => StockReservation::STATUS_PENDING,
                    'expires_at' => Carbon::now()->addMinutes($this->reservationMinutes()),
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
                    'reservation' => $reservation,
                ];
            }
        });

        return $orderItemsData;
    }

    /**
     * Pesanan sudah dibayar: reservasi menjadi 'reserved' sehingga tidak ikut dilepas oleh sweeper.
     * Stok sudah terpotong sejak checkout, jadi tidak ada perubahan stok di sini.
     *
     * @return int Jumlah reservasi yang dikonfirmasi
     */
    public function confirmForOrder(Order $order): int
    {
        return StockReservation::query()
            ->whereIn('order_item_id', $order->items()->select('id'))
            ->where('status', StockReservation::STATUS_PENDING)
            ->update(['status' => StockReservation::STATUS_RESERVED]);
    }

    /**
     * Lepas reservasi YATIM (tidak terhubung ke order): stok dikembalikan satu kali, idempotent.
     *
     * Reservasi milik sebuah order tidak boleh dilepas dari sini, karena order-nya akan tetap
     * 'pending' padahal stoknya sudah balik. Pembatalan order (OrderService::cancelOrder) adalah
     * satu-satunya jalur pengembalian stok untuk reservasi milik order.
     */
    public function releaseReservation(StockReservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            // Kunci baris reservasi: dua proses bersamaan tidak boleh sama-sama mengembalikan stok
            $locked = StockReservation::whereKey($reservation->getKey())->lockForUpdate()->first();

            if (! $locked || $locked->status !== StockReservation::STATUS_PENDING) {
                return;
            }

            if ($locked->order_item_id !== null) {
                throw new LogicException('Reservasi milik pesanan harus dilepas lewat OrderService::cancelOrder().');
            }

            $variant = $locked->variant;
            if ($variant) {
                $this->inventoryService->recordMovement(
                    variant: $variant,
                    quantity: $locked->quantity,
                    type: 'in',
                    userId: null,
                    note: 'Release reservation expiry',
                    referenceType: null,
                    referenceId: null,
                );
            }

            $locked->update(['status' => StockReservation::STATUS_RELEASED]);
        });
    }

    /**
     * Lepas semua reservasi yatim yang sudah lewat waktu.
     *
     * @return int Jumlah reservasi yang diproses
     */
    public function releaseExpiredOrphans(): int
    {
        $count = 0;

        StockReservation::expired()
            ->whereNull('order_item_id')
            ->get()
            ->each(function (StockReservation $reservation) use (&$count) {
                $this->releaseReservation($reservation);
                $count++;
            });

        return $count;
    }
}