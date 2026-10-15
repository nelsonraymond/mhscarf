<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class InventoryService
{
    /**
     * Record inventory movement and adjust variant stock atomically.
     *
     * @param  int  $quantity  Quantity moved (positive integer)
     * @param  string  $type  'in' | 'out' | 'adjustment' | 'return'
     * @param  int|null  $expectedStock  Hanya untuk 'adjustment' dari form admin: ditolak jika stok
     *                                   sekarang sudah berbeda dari yang dilihat admin saat membuka halaman.
     *
     * @throws RuntimeException bila stok basi atau stok tidak mencukupi untuk 'out'
     */
    public function recordMovement(
        ProductVariant $variant,
        int $quantity,
        string $type,
        ?int $userId = null,
        ?string $note = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $expectedStock = null
    ): InventoryMovement {
        if (! in_array($type, ['in', 'out', 'adjustment', 'return'], true)) {
            throw new InvalidArgumentException("Invalid inventory movement type: {$type}");
        }

        return DB::transaction(function () use (
            $variant,
            $quantity,
            $type,
            $userId,
            $note,
            $referenceType,
            $referenceId,
            $expectedStock
        ) {
            // Kunci baris varian dan baca stok terbaru dari database. Tanpa ini, dua proses yang
            // bersamaan (checkout, webhook, sweeper, pembatalan) bisa saling menimpa stok.
            $locked = ProductVariant::query()
                ->whereKey($variant->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $currentStock = (int) $locked->stock_qty;

            // Tolak penyesuaian berdasarkan angka basi (admin membuka form sebelum stok berubah)
            if ($expectedStock !== null && $expectedStock !== $currentStock) {
                throw new RuntimeException(
                    "Stok berubah sejak halaman dibuka (sekarang {$currentStock} pcs). Muat ulang halaman lalu coba lagi."
                );
            }

            $newStock = match ($type) {
                'in', 'return' => $currentStock + abs($quantity),
                'out' => $currentStock >= abs($quantity)
                    ? $currentStock - abs($quantity)
                    : throw new RuntimeException("Stok {$locked->name} tidak mencukupi ({$currentStock} pcs)."),
                'adjustment' => max(0, $quantity),
            };

            $movementQuantity = ($type === 'adjustment')
                ? ($newStock - $currentStock)
                : abs($quantity);

            $locked->update(['stock_qty' => $newStock]);

            // Sinkronkan instance milik pemanggil
            $variant->setAttribute('stock_qty', $newStock);
            $variant->syncOriginalAttribute('stock_qty');

            return InventoryMovement::create([
                'product_variant_id' => $variant->id,
                'type' => $type,
                'quantity' => $movementQuantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note,
                'created_by' => $userId,
            ]);
        });
    }

    /**
     * Explicitly set stock quantity with an adjustment movement.
     */
    public function setStock(
        ProductVariant $variant,
        int $newStock,
        ?int $userId = null,
        ?string $note = null
    ): InventoryMovement {
        return $this->recordMovement(
            variant: $variant,
            quantity: max(0, $newStock),
            type: 'adjustment',
            userId: $userId,
            note: $note ?? 'Manual stock adjustment from admin panel'
        );
    }
}