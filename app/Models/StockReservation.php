<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StockReservation extends Model
{
    protected $table = 'stock_reservations';

    protected $fillable = [
        'order_item_id',
        'product_variant_id',
        'quantity',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_RESERVED = 'reserved';

    public const STATUS_RELEASED = 'released';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Variant relation.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * OrderItem relation (may be null for pre‑checkout reservation).
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    /**
     * Scope for reservations that have passed their expiry while still pending.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('expires_at', '<', Carbon::now());
    }
}
