<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'order_item_id',
        'rating',
        'title',
        'comment',
        'photo_path',
        'is_verified_purchase',
        'is_published',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_verified_purchase' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public const EDIT_WINDOW_DAYS = 14;

/** "Nelson Raymond" -> "Nelson R." */
public function maskedName(): string
{
    $parts = preg_split('/\s+/', trim($this->user?->name ?? ''), -1, PREG_SPLIT_NO_EMPTY);
    if (! $parts) {
        return 'Pelanggan';
    }

    return count($parts) === 1
        ? $parts[0]
        : $parts[0].' '.mb_strtoupper(mb_substr($parts[1], 0, 1)).'.';
}

public function isEditable(): bool
{
    return $this->created_at && $this->created_at->gt(now()->subDays(self::EDIT_WINDOW_DAYS));
}
}
