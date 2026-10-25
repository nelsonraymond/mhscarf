@props(['category'])


@php
    // Prioritas 1: gambar kategori dari Supabase atau URL eksternal.
    $src = $category->image_url;

    // Prioritas 2: gambar statis berdasarkan slug.
    if (empty($src)) {
        $static = collect(['jpg', 'jpeg', 'png', 'webp'])
            ->map(fn ($ext) => "images/categories/{$category->slug}.{$ext}")
            ->first(fn ($path) => file_exists(public_path($path)));

        if ($static) {
            $src = asset($static);
        }
    }

    // Prioritas 3: gambar produk pertama sebagai fallback.
    if (empty($src)) {
        $src = $category->products?->first()?->primaryImage()?->image_url;
    }
@endphp


<a href="{{ route('shop.category', $category->slug) }}" class="group block">
    <div class="relative aspect-[4/5] overflow-hidden bg-[#F6EEF1]">
        @if ($src)
            <img src="{{ $src }}" alt="{{ $category->name }}" loading="lazy"
                 class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.04]"
                 onerror="this.hidden = true; this.parentElement.querySelector('[data-image-fallback]').hidden = false;">
            <div data-image-fallback hidden aria-hidden="true" class="absolute inset-0 bg-gradient-to-br from-[#FAF5F7] via-[#F6EEF1] to-[#EFE2E7]"></div>
        @else
            <div class="h-full w-full bg-gradient-to-br from-[#FAF5F7] via-[#F6EEF1] to-[#EFE2E7]"></div>
        @endif
    </div>
    <p class="mt-3 text-center text-sm tracking-wide text-ink transition-colors group-hover:text-pink-mauve">
        {{ $category->name }}
    </p>
</a>
