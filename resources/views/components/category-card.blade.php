@props(['category'])

@php
    // Prioritas: gambar terupload kategori -> file statis public/images/categories/{slug}.jpg -> foto produk pertama
    $src = null;

    if (! empty($category->image)) {
        $src = Storage::url($category->image);
    } elseif ($static = collect(['jpg', 'jpeg', 'png', 'webp'])
        ->map(fn ($ext) => "images/categories/{$category->slug}.{$ext}")
        ->first(fn ($p) => file_exists(public_path($p)))) {
        $src = asset($static);
    } else {
        $path = data_get($category, 'image_path')
            ?? $category->products?->first()?->primaryImage()?->image_path;
        $src = $path ? Storage::url($path) : null;
    }
@endphp

<a href="{{ route('shop.category', $category->slug) }}" class="group block">
    <div class="relative aspect-[4/5] overflow-hidden bg-[#F6EEF1]">
        @if ($src)
            <img src="{{ $src }}" alt="{{ $category->name }}" loading="lazy"
                 class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.04]">
        @else
            <div class="h-full w-full bg-gradient-to-br from-[#FAF5F7] via-[#F6EEF1] to-[#EFE2E7]"></div>
        @endif
    </div>
    <p class="mt-3 text-center text-sm tracking-wide text-ink transition-colors group-hover:text-pink-mauve">
        {{ $category->name }}
    </p>
</a>