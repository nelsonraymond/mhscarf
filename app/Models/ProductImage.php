<?php

namespace App\Models;

use Database\Factories\ProductImageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    /** @use HasFactory<ProductImageFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'variant_id',
        'image_path',
        'alt_text',
        'sort_order',
        'cloudinary_public_id',
        'is_primary',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function getImageUrlAttribute(): ?string
{
    if (empty($this->image_path)) {
        return null;
    }

    if (str_starts_with($this->image_path, 'http://')
        || str_starts_with($this->image_path, 'https://')) {
        return $this->image_path;
    }

    return \Illuminate\Support\Facades\Storage::disk('public')
        ->url($this->image_path);
}
}
