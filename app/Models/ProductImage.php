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

    // Pertahankan URL eksternal yang sudah tersimpan.
    if (preg_match('#^https?://#i', $this->image_path)) {
        return $this->image_path;
    }

    // Path gambar baru di Supabase Storage.
    $publicUrl = config('filesystems.disks.supabase.url');

    if (! empty($publicUrl)) {
        return rtrim($publicUrl, '/') . '/'
            . ltrim($this->image_path, '/');
    }

    // Fallback untuk konfigurasi lama.
    return \Illuminate\Support\Facades\Storage::disk('public')
        ->url($this->image_path);
}

}
