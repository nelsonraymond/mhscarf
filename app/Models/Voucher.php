<?php

namespace App\Models;

use Database\Factories\VoucherFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    /** @use HasFactory<VoucherFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'usage_limit' => 'integer',
            'usage_limit_per_user' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    public function typeLabel(): string
{
    return match ($this->type) {
        'percentage' => 'Persentase',
        'fixed' => 'Nominal tetap',
        'free_shipping' => 'Gratis ongkir',
        default => (string) $this->type,
    };
}

public function valueLabel(): string
{
    return match ($this->type) {
        'percentage' => rtrim(rtrim(number_format((float) $this->value, 2, ',', '.'), '0'), ',').'%',
        'fixed' => 'Rp'.number_format((float) $this->value, 0, ',', '.'),
        'free_shipping' => (float) $this->value > 0
            ? 'Maks. Rp'.number_format((float) $this->value, 0, ',', '.')
            : 'Ongkir penuh',
        default => '-',
    };
}

public function statusKey(): string
{
    if (! $this->is_active) {
        return 'inactive';
    }
    if ($this->expires_at && $this->expires_at->isPast()) {
        return 'expired';
    }
    if ($this->starts_at && $this->starts_at->isFuture()) {
        return 'scheduled';
    }
    if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
        return 'exhausted';
    }

    return 'active';
}

public function statusLabel(): string
{
    return match ($this->statusKey()) {
        'inactive' => 'Nonaktif',
        'expired' => 'Kedaluwarsa',
        'scheduled' => 'Terjadwal',
        'exhausted' => 'Kuota habis',
        default => 'Aktif',
    };
}

public function statusBadgeClass(): string
{
    return match ($this->statusKey()) {
        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'scheduled' => 'bg-blue-50 text-blue-700 border-blue-200',
        'expired', 'exhausted' => 'bg-amber-50 text-amber-700 border-amber-200',
        default => 'bg-stone-50 text-stone-600 border-stone-200',
    };
}
}