<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Validation\ValidationException;

class VoucherService
{
    public function normalize(?string $code): string
    {
        return strtoupper(trim((string) $code));
    }

    /**
     * Pratinjau untuk halaman checkout (tanpa kunci, tidak mengubah data).
     *
     * @return array{voucher: Voucher, discount: float}
     *
     * @throws ValidationException
     */
    public function preview(string $code, User $user, float $subtotal, float $shippingCost): array
    {
        $voucher = Voucher::where('code', $this->normalize($code))->first();

        return $this->evaluate($voucher, $user, $subtotal, $shippingCost);
    }

    /**
     * Dipanggil DI DALAM transaksi pembuatan pesanan. Baris voucher dikunci lalu semua syarat
     * dicek ulang, supaya dua pembeli tidak bisa memakai kuota terakhir secara bersamaan.
     *
     * @return array{voucher: Voucher, discount: float}
     *
     * @throws ValidationException
     */
    public function lockAndEvaluate(string $code, User $user, float $subtotal, float $shippingCost): array
    {
        $voucher = Voucher::where('code', $this->normalize($code))->lockForUpdate()->first();

        return $this->evaluate($voucher, $user, $subtotal, $shippingCost);
    }

    /**
     * Hitung potongan (rupiah bulat). Tidak pernah melebihi subtotal (atau ongkir untuk free_shipping).
     */
    public function calculate(Voucher $voucher, float $subtotal, float $shippingCost): float
    {
        $value = (float) $voucher->value;

        $discount = match ($voucher->type) {
            'percentage' => $subtotal * $value / 100,
            'fixed' => $value,
            'free_shipping' => $value > 0 ? min($shippingCost, $value) : $shippingCost,
            default => 0.0,
        };

        if ($voucher->type === 'percentage' && $voucher->max_discount_amount !== null) {
            $discount = min($discount, (float) $voucher->max_discount_amount);
        }

        $ceiling = $voucher->type === 'free_shipping' ? $shippingCost : $subtotal;

        return (float) round(max(0, min($discount, $ceiling)));
    }

    /**
     * Catat pemakaian setelah pesanan dibuat (di transaksi yang sama).
     */
    public function recordUsage(Voucher $voucher, User $user, Order $order, float $discount): void
    {
        VoucherUsage::create([
            'voucher_id' => $voucher->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'discount_amount' => $discount,
        ]);

        $voucher->increment('used_count');
    }

    /**
     * @return array{voucher: Voucher, discount: float}
     */
    protected function evaluate(?Voucher $voucher, User $user, float $subtotal, float $shippingCost): array
    {
        if (! $voucher || ! $voucher->is_active) {
            $this->fail('Kode voucher tidak valid.');
        }

        if ($voucher->starts_at && $voucher->starts_at->isFuture()) {
            $this->fail('Voucher ini belum berlaku.');
        }

        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            $this->fail('Voucher ini sudah kedaluwarsa.');
        }

        if ($voucher->min_order_amount !== null && $subtotal < (float) $voucher->min_order_amount) {
            $this->fail('Minimal belanja Rp'.number_format((float) $voucher->min_order_amount, 0, ',', '.').' untuk memakai voucher ini.');
        }

        if ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) {
            $this->fail('Kuota voucher ini sudah habis.');
        }

        if ($voucher->usage_limit_per_user !== null) {
            $usedByUser = VoucherUsage::where('voucher_id', $voucher->id)->where('user_id', $user->id)->count();
            if ($usedByUser >= $voucher->usage_limit_per_user) {
                $this->fail('Anda sudah mencapai batas pemakaian voucher ini.');
            }
        }

        $discount = $this->calculate($voucher, $subtotal, $shippingCost);

        if ($discount <= 0) {
            $this->fail('Voucher ini tidak memberikan potongan untuk pesanan Anda.');
        }

        return ['voucher' => $voucher, 'discount' => $discount];
    }

    protected function fail(string $message): never
    {
        throw ValidationException::withMessages(['voucher_code' => $message]);
    }
}