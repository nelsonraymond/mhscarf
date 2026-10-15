<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Display a listing of public catalog products.
     */
    public function index(Request $request, ?Category $category = null): View
    {
        // If visiting a specific category slug, verify that the category is active
        if ($category && ! $category->is_active) {
            abort(404);
        }

        $query = Product::query()
            ->active()
            ->whereHas('category', function ($q) {
                $q->active();
            })
            ->with([
                'category',
                'images' => function ($q) {
                    $q->orderByDesc('is_primary')->orderBy('sort_order');
                },
                'variants' => function ($q) {
                    $q->active();
                },
            ]);

        // Filter by category
        if ($category) {
            $query->where('category_id', $category->id);
        } elseif ($request->filled('category')) {
            $categorySlug = $request->input('category');
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Search by keyword
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('material', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('base_price', 'asc'),
            'price_desc' => $query->orderBy('base_price', 'desc'),
            'best_seller' => $query->orderByDesc('is_best_seller')->latest(),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::query()
            ->active()
            ->withCount(['products' => function ($q) {
                $q->active();
            }])
            ->orderBy('name')
            ->get();

        $selectedCategory = $category;

        return view('catalog.index', compact('products', 'categories', 'selectedCategory', 'sort'));
    }

    /**
     * Display public product detail.
     */
    public function show(Product $product): View
    {
        // Ensure product and its category are both active
        if (! $product->is_active || ! $product->category?->is_active) {
            abort(404);
        }

        $product->load([
            'category',
            'variants' => function ($q) {
                $q->active();
            },
            'images' => function ($q) {
                $q->orderByDesc('is_primary')->orderBy('sort_order');
            },
        ]);

        $averageRating = (float) $product->rating_avg;
        $reviewCount = (int) $product->rating_count;
        $ratingBreakdown = $product->reviews()
            ->where('is_published', true)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');
        $reviews = $product->reviews()
            ->where('is_published', true)
            ->with(['user', 'orderItem'])
            ->latest()
            ->paginate(5, ['*'], 'ulasan')
            ->fragment('ulasan');
        $totalStock = (int) $product->variants->sum('stock_qty');

        // Related products — real records only: active products in the same category, excluding this one.
        $relatedProducts = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->getKey())
            ->with([
                'category',
                'images' => function ($q) {
                    $q->orderByDesc('is_primary')->orderBy('sort_order');
                },
                'variants' => function ($q) {
                    $q->active();
                },
            ])
            ->latest()
            ->take(4)
            ->get();

        // Wishlist state for the current customer (existing wishlist data; presentation-level read).
        $wishlistedIds = auth()->check()
            ? auth()->user()->wishlists()->pluck('product_id')->all()
            : [];

        return view('catalog.show', compact(
            'product', 'averageRating', 'reviewCount', 'ratingBreakdown', 'reviews',
            'totalStock', 'relatedProducts', 'wishlistedIds'
        ));
    }
}
