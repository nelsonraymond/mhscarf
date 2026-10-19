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
        'cloudinary_public_id',
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

    // Cloudinary URL: return directly.
    if (str_starts_with($this->image, 'https://')) {
        return $this->image;
    }

    // Legacy local storage path.
    return Storage::disk('public')->url($this->image);
}
}
