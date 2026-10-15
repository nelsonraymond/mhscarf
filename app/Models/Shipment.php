<?php

namespace App\Models;

use Database\Factories\ShipmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    /** @use HasFactory<ShipmentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'courier',
        'service',
        'tracking_number',
        'shipping_cost',
        'status',
        'shipped_at',
        'delivered_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shipping_cost' => 'decimal:2',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Pengiriman',
            'picked_up' => 'Diambil Kurir',
            'in_transit' => 'Dalam Perjalanan',
            'shipped' => 'Dikirim',
            'delivered' => 'Tiba di Tujuan',
            'returned' => 'Dikembalikan',
            'cancelled' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $this->status ?? 'Menunggu Pengiriman')),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'picked_up', 'in_transit' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'shipped' => 'bg-sky-50 text-sky-700 border-sky-200',
            'delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'returned', 'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-stone-50 text-stone-700 border-stone-200',
        };
    }
}
