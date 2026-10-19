<x-layouts.app>
    @section('title', 'Wishlist Saya — MH Scarf')

    {{-- Tahap 5: migrated to shared app layout (navbar/footer/flash) + design tokens.
         Backend contract unchanged: wishlist.destroy (DELETE) / wishlist.toggle (POST). --}}

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="text-xs text-muted mb-6">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded transition">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-ink font-medium">Wishlist</li>
            </ol>
        </nav>

        {{-- Page header --}}
        <div class="mb-8">
            <p class="text-[11px] uppercase tracking-[0.2em] text-pink-mauve font-semibold mb-1">Akun Saya</p>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink">Daftar Keinginan</h1>
            <p class="text-sm text-muted mt-1.5">
                Simpan produk hijab yang Anda sukai untuk dibeli nanti
            </p>
        </div>

        {{-- Flash messages are rendered globally by the app layout (session('success')) --}}

        @if ($wishlists->isEmpty())
            {{-- Empty state with CTA back to the catalog --}}
            <div class="bg-white rounded-2xl border border-line p-10 sm:p-12 text-center max-w-lg mx-auto shadow-card">
                <div class="w-16 h-16 rounded-full bg-pink-soft/40 text-pink-deep flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.6-9.2-9A5.2 5.2 0 0112 6.5 5.2 5.2 0 0121.2 11C19 15.4 12 20 12 20z"/>
                    </svg>
                </div>
                <h2 class="font-display text-xl font-bold text-ink">Wishlist Anda Masih Kosong</h2>
                <p class="text-sm text-muted mt-1.5 mb-6">Tandai hijab favorit dengan ikon hati untuk menyimpannya di sini.</p>
                <a href="{{ route('shop.index') }}"
                    class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-pink-deep hover:bg-pink-mauve text-white font-medium text-sm transition shadow-sm min-h-[48px]">
                    Jelajahi Koleksi Hijab
                </a>
            </div>
        @else
            {{-- Responsive grid, consistent with the catalog product cards.
                 Each card is a standalone <article>: detail link + delete form as siblings (no nested forms). --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach ($wishlists as $wish)
                    @php $product = $wish->product; @endphp
                    @if ($product)
                        <article class="group relative flex flex-col bg-white border border-line/80 rounded-md overflow-hidden hover:border-pink-mauve/50 transition-all duration-300 hover:shadow-xs">

                            {{-- Thumbnail (4:5, primary image from eager-loaded relation) --}}
                            <div class="relative aspect-[4/5] w-full overflow-hidden bg-[#FAF6F7]">
                                <a href="{{ route('shop.product', $product->slug) }}" class="block w-full h-full" aria-label="{{ $product->name }}">
                                    @php $primaryImg = $product->primaryImage(); @endphp
                                    @if ($primaryImg)
                                        <img src="{{ $primaryImg->image_url }}"
                                            alt="{{ $primaryImg->alt_text ?? $product->name }}"
                                            class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03]" loading="lazy">
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-b from-[#FAF6F7] to-[#F3EBEE]">
                                            <span class="text-[10px] tracking-[0.25em] uppercase text-muted font-medium mb-1">
                                                {{ $product->category?->name ?? 'Koleksi Hijab' }}
                                            </span>
                                            <span class="font-display italic text-sm text-ink/80 leading-snug line-clamp-2">{{ $product->name }}</span>
                                        </div>
                                    @endif
                                </a>

                                {{-- Remove action — existing DELETE /wishlist/{product} contract --}}
                                <form method="POST" action="{{ route('wishlist.destroy', $product) }}" class="absolute top-2.5 right-2.5 z-10">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus dari Wishlist" aria-label="Hapus {{ $product->name }} dari wishlist"
                                        class="w-9 h-9 rounded-full bg-white/85 backdrop-blur border border-line inline-flex items-center justify-center text-pink-deep transition duration-300 hover:border-pink-deep hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep cursor-pointer">
                                        <svg class="w-[18px] h-[18px] fill-pink-deep" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.6-9.2-9A5.2 5.2 0 0112 6.5 5.2 5.2 0 0121.2 11C19 15.4 12 20 12 20z"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>

                            {{-- Product info: name, price, stock status (real data via model helpers) --}}
                            <div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between gap-3">
                                <div class="space-y-1 min-w-0">
                                    <p class="text-[11px] tracking-wider uppercase text-muted font-medium line-clamp-1">
                                        {{ $product->material ?? $product->category?->name }}
                                    </p>
                                    <h3 class="text-sm font-medium text-ink leading-snug">
                                        <a href="{{ route('shop.product', $product->slug) }}" class="hover:text-pink-mauve transition-colors line-clamp-1">
                                            {{ $product->name }}
                                        </a>
                                    </h3>
                                </div>

                                <div class="space-y-2 pt-2 border-t border-line/60">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <x-price-display :price="$product->base_price" :compare-at="(float) $product->compare_at_price" />
                                        @if ($product->hasAvailableStock())
                                            <span class="text-[10px] font-normal text-muted/90 shrink-0">Tersedia</span>
                                        @else
                                            <span class="text-[10px] font-medium text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded-xs shrink-0">Habis</span>
                                        @endif
                                    </div>

                                    {{-- Cart add requires a specific variant id (cart.items.store contract),
                                         so the CTA leads to the product detail page where variants are chosen. --}}
                                    <a href="{{ route('shop.product', $product->slug) }}"
                                        class="w-full inline-flex items-center justify-center py-2.5 rounded-xl bg-cream hover:bg-pink-soft/40 border border-line text-xs font-medium text-ink text-center transition min-h-[40px]">
                                        Lihat Pilihan Varian
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endif
                @endforeach
            </div>

            @if ($wishlists->hasPages())
                <div class="mt-10">
                    <x-pagination :paginator="$wishlists" />
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
