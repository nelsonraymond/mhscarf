<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(protected ReviewService $reviews) {}

    public function create(Request $request, OrderItem $orderItem): View|RedirectResponse
    {
        $orderItem->load('order', 'product', 'review');
        abort_unless($orderItem->order->user_id === $request->user()->id, 403);

        if ($orderItem->review) {
            return redirect()->route('reviews.edit', $orderItem->review);
        }

        if (! $this->reviews->canReview($request->user(), $orderItem)) {
            return redirect()->route('orders.show', $orderItem->order)
                ->with('error', 'Ulasan hanya dapat diberikan setelah pesanan tiba.');
        }

        return view('reviews.form', ['orderItem' => $orderItem, 'review' => null]);
    }

    public function store(Request $request, OrderItem $orderItem): RedirectResponse
    {
        $orderItem->load('order');
        abort_unless($orderItem->order->user_id === $request->user()->id, 403);

        if (! $this->reviews->canReview($request->user(), $orderItem)) {
            return redirect()->route('orders.show', $orderItem->order)
                ->with('error', 'Produk ini tidak dapat diulas.');
        }

        $data = $this->validated($request);

        try {
            $this->reviews->create($request->user(), $orderItem, $data);
        } catch (UniqueConstraintViolationException) {
            return redirect()->route('orders.show', $orderItem->order)
                ->with('error', 'Anda sudah mengulas produk ini.');
        }

        return redirect()->route('orders.show', $orderItem->order)
            ->with('success', 'Terima kasih! Ulasan Anda sudah ditampilkan.');
    }

    public function edit(Request $request, Review $review): View|RedirectResponse
    {
        abort_unless($review->user_id === $request->user()->id, 403);
        $review->load('orderItem.order', 'orderItem.product');

        if (! $review->isEditable()) {
            return redirect()->route('orders.show', $review->orderItem->order)
                ->with('error', 'Batas waktu untuk mengubah ulasan sudah lewat.');
        }

        return view('reviews.form', ['orderItem' => $review->orderItem, 'review' => $review]);
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        abort_unless($review->user_id === $request->user()->id, 403);
        $review->load('orderItem.order');

        if (! $review->isEditable()) {
            return redirect()->route('orders.show', $review->orderItem->order)
                ->with('error', 'Batas waktu untuk mengubah ulasan sudah lewat.');
        }

        $this->reviews->update($review, $this->validated($request));

        return redirect()->route('orders.show', $review->orderItem->order)
            ->with('success', 'Ulasan Anda berhasil diperbarui.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Silakan pilih jumlah bintang.',
            'rating.between' => 'Rating harus antara 1 sampai 5 bintang.',
            'comment.max' => 'Ulasan maksimal 1000 karakter.',
        ]);
    }
}