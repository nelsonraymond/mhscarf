<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    
public function getImageUrlAttribute(): ?string
{
    if (empty($this->image)) {
        $static = collect(['jpg', 'jpeg', 'png', 'webp'])
            ->map(fn ($ext) => "images/categories/{$this->slug}.{$ext}")
            ->first(fn ($path) => file_exists(public_path($path)));

        return $static ? asset($static) : null;
    }

    // URL gambar lama dari Cloudinary atau layanan eksternal.
    if (preg_match('#^https?://#i', $this->image)) {
        return $this->image;
    }

    // Gambar baru yang disimpan di Supabase Storage.
    $publicUrl = config('filesystems.disks.supabase.url');

    if (! empty($publicUrl)) {
        return rtrim($publicUrl, '/') . '/' . ltrim($this->image, '/');
    }

    // Kompatibilitas untuk gambar lama di local public storage.
    return Storage::disk('public')->url($this->image);
}

}
