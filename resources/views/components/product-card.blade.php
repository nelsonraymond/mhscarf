@props(['product'])

@php
    $primaryImg = $product->primaryImage();
    $secondaryImg = $product->images->first(fn ($img) => $primaryImg && $img->id !== $primaryImg->id);
    $activeVariants = $product->variants->where('is_active', true);
    $colorVariants = $activeVariants->filter(fn ($v) => !empty($v->color_hex))->unique('color_hex')->take(4);
    $discountPercent = ($product->compare_at_price && $product->compare_at_price > $product->base_price)
        ? round((($product->compare_at_price - $product->base_price) / $product->compare_at_price) * 100)
        : null;
@endphp

{{-- Product Card — DESIGN.md v3 §8: Fashion retail card with 4:5 image, clean typography, zero clutter --}}
<article {{ $attributes->merge(['class' => 'group relative flex flex-col bg-white border border-line/80 rounded-md overflow-hidden hover:border-pink-mauve/50 transition-all duration-300 hover:shadow-xs']) }}>

    {{-- Product Image Container (4:5 Aspect Ratio per §8) --}}
    <div class="relative aspect-[4/5] w-full overflow-hidden bg-[#FAF6F7]">
        <a href="{{ route('shop.product', $product->slug) }}" class="block w-full h-full" aria-label="{{ $product->name }}">
            @if ($primaryImg)
                <img src="{{ Storage::url($primaryImg->image_path) }}" alt="{{ $primaryImg->alt_text ?? $product->name }}"
                    class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03]" loading="lazy">
                @if ($secondaryImg)
                    <img src="{{ Storage::url($secondaryImg->image_path) }}" alt="{{ $secondaryImg->alt_text ?? $product->name }}"
                        class="absolute inset-0 w-full h-full object-cover opacity-0 group-hover:opacity-100 transition-opacity duration-500 ease-out" loading="lazy">
                @endif
            @else
                {{-- Tasteful neutral editorial image placeholder per §10 (warm tone, fashion typography, NO flowers or debug text) --}}
                <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-b from-[#FAF6F7] to-[#F3EBEE] transition-transform duration-500 ease-out group-hover:scale-[1.02]">
                    <span class="text-[10px] tracking-[0.25em] uppercase text-muted font-medium mb-1">
                        {{ $product->category?->name ?? 'Koleksi Hijab' }}
                    </span>
                    <span class="font-display italic text-sm sm:text-base text-ink/80 leading-snug line-clamp-2">
                        {{ $product->name }}
                    </span>
                    @if ($product->material)
                        <span class="text-[11px] text-pink-mauve mt-2 tracking-wide font-normal">
                            {{ $product->material }}
                        </span>
                    @endif
                </div>
            @endif
        </a>

        {{-- Product Badges (Top-Left, restrained rectangular tags) --}}
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

        {{-- Wishlist Toggle (Top-Right, subtle micro-action) --}}
        <x-wishlist-button :product="$product"
            class="absolute top-2.5 right-2.5 z-10 opacity-90 sm:opacity-0 sm:group-hover:opacity-100 sm:focus-within:opacity-100 transition-opacity duration-200" />
    </div>

    {{-- Product Metadata Stack --}}
    <div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between gap-3">
        <div class="space-y-1">
            {{-- Category / Material micro-label --}}
            <p class="text-[11px] tracking-wider uppercase text-muted font-medium line-clamp-1">
                {{ $product->material ?? $product->category?->name }}
            </p>

            {{-- Product Name --}}
            <h3 class="text-sm font-medium text-ink leading-snug">
                <a href="{{ route('shop.product', $product->slug) }}" class="hover:text-pink-mauve transition-colors line-clamp-1">
                    {{ $product->name }}
                </a>
            </h3>

            @if ($product->rating_count > 0)
        <div class="flex items-center gap-1.5 pt-0.5">
        <x-star-rating :value="$product->rating_avg" class="text-[11px]" />
        <span class="text-[11px] text-muted">{{ number_format($product->rating_avg, 1) }} ({{ $product->rating_count }})</span>
    </div>
@endif
        </div>

        <div class="space-y-2 pt-2 border-t border-line/60">
            {{-- Price Display --}}
            <div class="flex items-baseline justify-between gap-2">
                <x-price-display :price="$product->base_price" :compare-at="(float) $product->compare_at_price" />

                {{-- Stock Availability --}}
                @if ($product->hasAvailableStock())
                    <span class="text-[10px] font-normal text-muted/90 shrink-0">Tersedia</span>
                @else
                    <span class="text-[10px] font-medium text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded-xs shrink-0">Habis</span>
                @endif
            </div>

            {{-- Color Variants / Options hint --}}
            @if ($colorVariants->isNotEmpty())
                <div class="flex items-center gap-1.5 pt-0.5" title="{{ $colorVariants->pluck('color_name')->join(', ') }}">
                    @foreach ($colorVariants as $cVar)
                        <span class="w-2.5 h-2.5 rounded-full border border-line shadow-2xs"
                            style="background-color: {{ $cVar->color_hex }};"
                            aria-label="{{ $cVar->color_name }}"></span>
                    @endforeach
                    @if ($activeVariants->count() > $colorVariants->count())
                        <span class="text-[10px] text-muted ml-0.5">+{{ $activeVariants->count() - $colorVariants->count() }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</article>
