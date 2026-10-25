<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'checkout_token',
        'status',
        'payment_status',
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'grand_total',
        'voucher_code',
        'voucher_discount',
        'shipping_recipient_name',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_province',
        'shipping_postal_code',
        'customer_note',
        'placed_at',
        'delivered_at',
        'review_reminded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'voucher_discount' => 'decimal:2',
            'placed_at' => 'datetime',
            'delivered_at' => 'datetime',
            'review_reminded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest('id');
    }

    public function voucherUsages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Pembayaran',
            'confirmed' => 'Terkonfirmasi',
            'processing' => 'Diproses',
            'packed' => 'Dikemas',
            'shipped' => 'Dikirim',
            'delivered' => 'Tiba di Tujuan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'pending' => 'Belum Dibayar',
            'paid' => 'Sudah Dibayar',
            'failed' => 'Gagal',
            'expired' => 'Kedaluwarsa',
            'refunded' => 'Dikembalikan',
            default => ucfirst($this->payment_status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
            'processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'packed' => 'bg-purple-50 text-purple-700 border-purple-200',
            'shipped' => 'bg-sky-50 text-sky-700 border-sky-200',
            'delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'completed' => 'bg-green-50 text-green-700 border-green-200',
            'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-stone-50 text-stone-700 border-stone-200',
        };
    }

    public function paymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'failed', 'expired' => 'bg-rose-50 text-rose-700 border-rose-200',
            'refunded' => 'bg-purple-50 text-purple-700 border-purple-200',
            default => 'bg-stone-50 text-stone-700 border-stone-200',
        };
    }

    public function formattedSubtotal(): string
    {
        return 'Rp '.number_format($this->subtotal, 0, ',', '.');
    }

    public function formattedShippingCost(): string
    {
        return 'Rp '.number_format($this->shipping_cost, 0, ',', '.');
    }

    public function formattedGrandTotal(): string
    {
        return 'Rp '.number_format($this->grand_total, 0, ',', '.');
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Whether the customer may still cancel this order themselves.
     * Mirrors the cancellable states enforced by OrderService::cancelOrder().
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'processing', 'packed'], true);
    }

    public function isReviewable(): bool
{
    return in_array($this->status, ['delivered', 'completed'], true);
}

    /**
     * Get tracking timeline steps for visual rendering.
     *
     * @return array<int, array{key: string, title: string, description: string, is_completed: bool, is_current: bool}>
     */
    public function trackingTimeline(): array
    {
        $allSteps = [
            ['key' => 'pending', 'title' => 'Pesanan Dibuat', 'desc' => 'Menunggu verifikasi pembayaran'],
            ['key' => 'confirmed', 'title' => 'Terkonfirmasi', 'desc' => 'Pesanan telah diverifikasi'],
            ['key' => 'processing', 'title' => 'Diproses', 'desc' => 'Pesanan sedang disiapkan'],
            ['key' => 'packed', 'title' => 'Dikemas', 'desc' => 'Paket siap diserahkan ke kurir'],
            ['key' => 'shipped', 'title' => 'Dikirim', 'desc' => 'Paket dalam perjalanan'],
            ['key' => 'delivered', 'title' => 'Tiba di Tujuan', 'desc' => 'Paket telah sampai di alamat'],
            ['key' => 'completed', 'title' => 'Selesai', 'desc' => 'Pesanan selesai'],
        ];

        $stepKeys = array_column($allSteps, 'key');
        $currentIndex = array_search($this->status, $stepKeys, true);

        // If cancelled or unknown, currentIndex will be false
        return array_map(function ($step, $idx) use ($currentIndex) {
            $isCompleted = ($currentIndex !== false && $idx <= $currentIndex);
            $isCurrent = ($currentIndex !== false && $idx === $currentIndex);

            return [
                'key' => $step['key'],
                'title' => $step['title'],
                'description' => $step['desc'],
                'is_completed' => $isCompleted,
                'is_current' => $isCurrent,
            ];
        }, $allSteps, array_keys($allSteps));
    }
}
