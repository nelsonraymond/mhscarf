@props(['product'])

@php
    $primaryImg = $product->primaryImage();
    $secondaryImg = $product->images->first(fn ($img) => $primaryImg && $img->id !== $primaryImg->id);
    $hasCompare = $product->compare_at_price && $product->compare_at_price > $product->base_price;
    $discountPercent = $hasCompare
        ? round((($product->compare_at_price - $product->base_price) / $product->compare_at_price) * 100)
        : null;
@endphp

<article {{ $attributes->merge(['class' => 'group relative flex flex-col']) }}>

    <div class="relative aspect-square w-full overflow-hidden bg-cream">
        <a href="{{ route('shop.product', $product->slug) }}" class="block w-full h-full" aria-label="{{ $product->name }}">
            @if ($primaryImg)
                <img src="{{ $primaryImg->image_url }}" alt="{{ $primaryImg->alt_text ?? $product->name }}"
                    class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03]" loading="lazy"
                    onerror="this.hidden = true; this.parentElement.parentElement.querySelector('[data-image-fallback]').hidden = false;">
                @if ($secondaryImg)
                    <img src="{{ $secondaryImg->image_url }}" alt="{{ $secondaryImg->alt_text ?? $product->name }}"
                        class="absolute inset-0 w-full h-full object-cover opacity-0 group-hover:opacity-100 transition-opacity duration-500 ease-out" loading="lazy"
                        onerror="this.hidden = true;">
                @endif
                <span data-image-fallback hidden role="img" aria-label="Foto {{ $product->name }} belum tersedia"
                    class="absolute inset-0 flex items-center justify-center bg-cream px-4 text-center text-xs text-muted">
                    Foto produk belum tersedia
                </span>
            @else
                <div class="w-full h-full flex items-center justify-center bg-cream text-[10px] tracking-widest uppercase text-pink-deep">
                    MH Scarf
                </div>
            @endif
        </a>

        {{-- Badge --}}
        <div class="absolute top-2.5 left-2.5 flex flex-col items-start gap-1 pointer-events-none z-10">
            @if ($product->is_best_seller)
                <span class="px-2 py-0.5 text-[9px] sm:text-[10px] font-semibold tracking-wider uppercase bg-pink-mauve text-white rounded-xs">
                    Best Seller
                </span>
            @elseif ($discountPercent)
                <span class="px-2 py-0.5 text-[9px] sm:text-[10px] font-semibold tracking-wider uppercase bg-rose-600 text-white rounded-xs">
                    -{{ $discountPercent }}%
                </span>
            @elseif ($product->is_featured)
                <span class="px-2 py-0.5 text-[9px] sm:text-[10px] font-semibold tracking-wider uppercase bg-ink text-white rounded-xs">
                    Pilihan
                </span>
            @endif
        </div>

        <x-wishlist-button :product="$product"
            class="absolute top-2.5 right-2.5 z-10 opacity-90 sm:opacity-0 sm:group-hover:opacity-100 sm:focus-within:opacity-100 transition-opacity duration-200" />
    </div>

    {{-- Nama, rating, harga: satu keluarga font, warna ink, bobot regular --}}
    <div class="pt-5 pb-2 space-y-2 text-ink">
        <h3 class="text-[15px] font-normal leading-snug">
            <a href="{{ route('shop.product', $product->slug) }}" class="hover:text-pink-deep transition-colors line-clamp-2">
                {{ $product->name }}
            </a>
        </h3>

        @if ($product->rating_count > 0)
            <div class="flex items-center gap-1.5 text-[13px] font-normal">
                <x-star-rating :value="$product->rating_avg" class="text-[13px]" />
                <span>{{ number_format($product->rating_avg, 1) }} ({{ $product->rating_count }})</span>
            </div>
        @endif

        <p class="flex items-baseline gap-2 text-[17px] font-normal">
            <span>Rp {{ number_format($product->base_price, 0, ',', '.') }}</span>
            @if ($hasCompare)
                <span class="text-sm text-muted line-through">Rp {{ number_format($product->compare_at_price, 0, ',', '.') }}</span>
            @endif
        </p>
    </div>
</article>
