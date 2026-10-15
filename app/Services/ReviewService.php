<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function canReview(User $user, OrderItem $item): bool
    {
        $item->loadMissing('order');

        return $item->order
            && $item->order->user_id === $user->id
            && $item->order->isReviewable()
            && ! $item->review()->withTrashed()->exists();
    }

    /** @param array{rating:int, comment?:?string} $data */
    public function create(User $user, OrderItem $item, array $data): Review
    {
        return DB::transaction(function () use ($user, $item, $data) {
            $review = Review::create([
                'product_id' => $item->product_id,
                'user_id' => $user->id,
                'order_item_id' => $item->id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'is_verified_purchase' => true,
                'is_published' => true,
            ]);

            $this->refreshProductRating($review->product_id);

            return $review;
        });
    }

    /** @param array{rating:int, comment?:?string} $data */
    public function update(Review $review, array $data): Review
    {
        return DB::transaction(function () use ($review, $data) {
            $review->update([
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]);
            $this->refreshProductRating($review->product_id);

            return $review;
        });
    }

    /** Dipakai admin untuk menyembunyikan / menampilkan ulasan. */
    public function setPublished(Review $review, bool $published): Review
{
    return DB::transaction(function () use ($review, $published) {
        $review->update(['is_published' => $published]);
        $this->refreshProductRating($review->product_id);

        return $review;
    });
}

    public function refreshProductRating(int $productId): void
{
    // Serialisasi penghitungan ulang per produk. Harus dipanggil di dalam transaksi.
    Product::whereKey($productId)->lockForUpdate()->first();

    $stats = Review::where('product_id', $productId)
        ->where('is_published', true)
        ->selectRaw('COUNT(*) as c, COALESCE(AVG(rating), 0) as a')
        ->first();

    Product::whereKey($productId)->update([
        'rating_count' => (int) $stats->c,
        'rating_avg' => round((float) $stats->a, 2),
    ]);
}
}