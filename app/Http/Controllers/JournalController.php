<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function show(string $slug): View
    {
        $articles = collect(config('journal.articles'));

        $index = $articles->search(fn ($a) => $a['slug'] === $slug);
        abort_if($index === false, 404);

        $article = $articles[$index];

        $products = Product::query()
            ->active()
            ->whereHas('category', fn ($q) => $q->active())
            ->when($article['product_query'] !== '', function ($q) use ($article) {
                $term = '%' . $article['product_query'] . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('material', 'like', $term));
            })
            ->with(['category', 'images', 'variants' => fn ($q) => $q->active()])
            ->latest()
            ->take(4)
            ->get();

        return view('journal.show', [
            'article'     => $article,
            'publishedAt' => Carbon::parse($article['date'])->locale('id')->translatedFormat('j F Y'),
            'products'    => $products,
            'others'      => $articles->where('slug', '!=', $slug)->values(),
        ]);
    }
}