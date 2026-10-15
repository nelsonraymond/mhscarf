<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(protected ReviewService $reviews) {}

    public function index(Request $request): View
    {
        $query = Review::query()->with(['user', 'product'])->latest();

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }

        if ($request->input('visibility') === 'published') {
            $query->where('is_published', true);
        } elseif ($request->input('visibility') === 'hidden') {
            $query->where('is_published', false);
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('comment', 'like', "%{$s}%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%"));
            });
        }

        return view('admin.reviews.index', ['reviews' => $query->paginate(15)->withQueryString()]);
    }

    public function toggle(Review $review): RedirectResponse
    {
        $this->reviews->setPublished($review, ! $review->is_published);

        return back()->with('success', $review->is_published
            ? 'Ulasan ditampilkan kembali.'
            : 'Ulasan disembunyikan dari halaman produk.');
    }
}