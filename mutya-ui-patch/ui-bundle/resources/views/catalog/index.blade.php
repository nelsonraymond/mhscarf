<x-layouts.app>
    @section('title', ($selectedCategory ? $selectedCategory->name . ' — ' : 'Semua Koleksi — ') . 'MH Scarf Store')

    {{-- ================= SHOP PAGE — DESIGN.md v3: product-first, editorial-minimal =================
         Reuses shared components (layout navbar/footer, ProductCard) and the proven CatalogController
         query logic (category filter, search q, sort, paginate(12)->withQueryString()). No fake controls.

         Tahap 2 additions: active-filter chips + reset, sticky mobile filter/sort sheet (DESIGN.md §28/§38),
         desktop inline search, branded pagination component. All controls map to real backend params only
         (q / category / sort) — no decorative price/color/material filters. --}}
        @php
            // Active filter state shared by chips + mobile sheet (real query params supported by CatalogController)
            $shopQ = request('q');
            $shopSortLabel = ['price_asc' => 'Harga: Terendah', 'price_desc' => 'Harga: Tertinggi', 'best_seller' => 'Best Seller'][$sort] ?? null;
            $activeSlug = $selectedCategory?->slug ?? request('category');
            $hasActiveFilters = filled($shopQ) || filled($activeSlug) || $sort !== 'newest';
            $resetUrl = route('shop.index');
        @endphp
    {{-- Mobile filter sheet styles live in resources/css/app.css (plain CSS keyed to
         #shop-filtersheet / #shop-filtersheet-backdrop + .is-open state toggled by the
         script at the bottom of this page). Transitions honor prefers-reduced-motion;
         the sheet is display:none from lg upward. --}}

    <div class="bg-white border-b border-line">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">

            {{-- Breadcrumb — concise page context --}}
            <nav aria-label="Breadcrumb" class="text-[11px] text-muted mb-6">
                <ol class="flex items-center gap-1.5 flex-wrap">
                    <li><a href="{{ route('home') }}" class="hover:text-pink-deep transition">Beranda</a></li>
                    <li aria-hidden="true" class="text-line">/</li>
                    @if ($selectedCategory)
                        <li><a href="{{ route('shop.index') }}" class="hover:text-pink-deep transition">Koleksi</a></li>
                        <li aria-hidden="true" class="text-line">/</li>
                        <li aria-current="page" class="text-ink">{{ $selectedCategory->name }}</li>
                    @else
                        <li aria-current="page" class="text-ink">Koleksi</li>
                    @endif
                    @if (request('q'))
                        <li aria-hidden="true" class="text-line">/</li>
                        <li aria-current="page" class="text-ink">“{{ request('q') }}”</li>
                    @endif
                </ol>
            </nav>

            {{-- Heading + supporting copy + accurate count --}}
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[11px] tracking-[0.3em] uppercase text-pink-mauve font-medium mb-2">
                        {{ $selectedCategory ? 'Kategori' : 'Boutique Collection' }}
                    </p>
                    <h1 class="font-display text-3xl md:text-4xl font-medium text-ink leading-tight">
                        {{ $selectedCategory?->name ?? 'Semua Koleksi Hijab' }}
                    </h1>
                    <p class="mt-2 text-sm text-muted leading-relaxed max-w-xl">
                        {{ $selectedCategory?->description ?? 'Sentuhan kelembutan dan elegansi modern untuk menyempurnakan setiap penampilan Anda.' }}
                    </p>
                </div>

                <p class="text-xs text-muted shrink-0 sm:text-right sm:pb-1">
                    <span class="text-ink font-semibold">{{ number_format($products->total(), 0, ',', '.') }}</span>
                    produk ditemukan
                </p>
            </div>

            {{-- Active filter chips + reset (Tahap 2) — only shown when filters/search/sort are active --}}
            @if ($hasActiveFilters)
                <div class="mt-6 flex items-center flex-wrap gap-2" aria-label="Filter aktif">
                    <span class="text-[11px] uppercase tracking-wider text-muted font-medium">Filter aktif:</span>
                    @if ($selectedCategory || request('category'))
                        <a href="{{ route('shop.index', array_filter(['q' => $shopQ, 'sort' => $sort !== 'newest' ? $sort : null])) }}"
                            class="inline-flex items-center gap-1.5 min-h-[32px] px-3 rounded-full bg-pink-soft/50 border border-pink-mauve/30 text-xs text-pink-deep hover:border-pink-deep transition">
                            {{ $selectedCategory?->name ??ucfirst(request('category')) }}
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                            <span class="sr-only">(hapus filter kategori)</span>
                        </a>
                    @endif
                    @if ($shopQ)
                        <a href="{{ route('shop.index', array_filter(['category' => $activeSlug ?? null, 'sort' => $sort !== 'newest' ? $sort : null])) }}"
                            class="inline-flex items-center gap-1.5 min-h-[32px] px-3 rounded-full bg-pink-soft/50 border border-pink-mauve/30 text-xs text-pink-deep hover:border-pink-deep transition">
                            “{{ $shopQ }}”
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                            <span class="sr-only">(hapus pencarian)</span>
                        </a>
                    @endif
                    @if ($shopSortLabel)
                        <a href="{{ route('shop.index', array_filter(['category' => $activeSlug ?? null, 'q' => $shopQ])) }}"
                            class="inline-flex items-center gap-1.5 min-h-[32px] px-3 rounded-full bg-pink-soft/50 border border-pink-mauve/30 text-xs text-pink-deep hover:border-pink-deep transition">
                            {{ $shopSortLabel }}
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                            <span class="sr-only">(kembalikan urutan ke Terbaru)</span>
                        </a>
                    @endif
                    <a href="{{ $resetUrl }}" class="text-xs text-muted underline underline-offset-4 hover:text-pink-deep transition min-h-[32px] inline-flex items-center px-1">
                        Hapus semua
                    </a>
                </div>
            @endif
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10">

        {{-- ============ Category navigation — real DB categories, link-based.
             Mobile: chip row inside the filter bottom-sheet (opens via sticky control below).
             Desktop (lg+): inline chip row + inline search toolbar. --}}
        @php
            $keepQuery = array_filter(['q' => request('q'), 'sort' => request('sort')]);
        @endphp

        {{-- Sticky mobile controls: [ Filter ] per DESIGN.md §28/§38 (slide-over/bottom sheet) --}}
        <div class="lg:hidden sticky top-14 sm:top-16 z-30 -mx-4 px-4 py-2 bg-cream/90 backdrop-blur border-b border-line/60">
            <div class="flex items-center gap-2">
                <button type="button" id="shop-open-filtersheet"
                    class="min-h-[44px] flex-1 inline-flex items-center justify-center gap-2 px-4 rounded-xl bg-white border border-line text-sm font-medium text-ink hover:border-pink-deep transition focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep"
                    aria-haspopup="dialog" aria-controls="shop-filtersheet" aria-expanded="false">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10m-7 6h4"/>
                    </svg>
                    Filter &amp; Urutkan
                    @if ($hasActiveFilters)
                        <span class="min-w-[20px] h-5 px-1.5 inline-flex items-center justify-center rounded-full bg-pink-deep text-white text-[10px] font-semibold">
                            {{ collect([$activeSlug ? 1 : 0, $shopQ ? 1 : 0, $shopSortLabel ? 1 : 0])->sum() }}
                        </span>
                    @endif
                </button>
                @if ($shopSortLabel)
                    <span class="shrink-0 min-h-[44px] inline-flex items-center px-3 rounded-xl bg-pink-soft/50 border border-pink-mauve/30 text-xs text-pink-deep">
                        {{ $shopSortLabel }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Mobile bottom-sheet: search + categories + sort (all real params: q / category / sort) --}}
        <div id="shop-filtersheet-backdrop" data-shop-close></div>
        <div id="shop-filtersheet" role="dialog" aria-modal="true" aria-label="Filter katalog">
            <div class="sticky top-0 bg-white/95 backdrop-blur border-b border-line px-5 pt-3 pb-3 rounded-t-2xl flex items-center justify-between">
                <p class="font-display text-lg text-ink">Filter &amp; Urutkan</p>
                <button type="button" data-shop-close
                    class="w-11 h-11 -mr-2 inline-flex items-center justify-center rounded-full text-muted hover:text-ink hover:bg-pink-soft transition focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep"
                    aria-label="Tutup panel filter">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
            <div class="px-5 py-5 space-y-7">
                <form method="GET" action="{{ $selectedCategory ? route('shop.category', $selectedCategory) : route('shop.index') }}" class="space-y-6">
                    {{-- Search --}}
                    <div>
                        <label for="sheet-search" class="block text-[11px] uppercase tracking-[0.2em] text-muted font-medium mb-2.5">Cari</label>
                        <div class="relative">
                            <input id="sheet-search" type="search" name="q" value="{{ $shopQ }}" placeholder="Cari hijab…"
                                class="w-full min-h-[44px] pl-9 pr-3 rounded-xl border border-line bg-white text-sm text-ink placeholder:text-muted/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep transition">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Categories (chips link directly — real DB categories). Single source of truth: catalog/_chips --}}
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.2em] text-muted font-medium mb-2.5">Kategori</p>
                        <div class="flex flex-wrap gap-2">
                            @include('catalog._chips')
                        </div>
                    </div>

                    {{-- Sort --}}
                    <div>
                        <label for="sheet-sort" class="block text-[11px] uppercase tracking-[0.2em] text-muted font-medium mb-2.5">Urutkan</label>
                        <select id="sheet-sort" name="sort" class="w-full min-h-[44px] px-3 rounded-xl border border-line bg-white text-sm text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep cursor-pointer">
                            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Harga: Terendah</option>
                            <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Harga: Tertinggi</option>
                            <option value="best_seller" {{ $sort === 'best_seller' ? 'selected' : '' }}>Best Seller</option>
                        </select>
                    </div>

                    <div class="flex gap-3 pt-1">
                        <a href="{{ $resetUrl }}" class="min-h-[48px] flex-1 inline-flex items-center justify-center rounded-xl border border-line text-sm font-medium text-muted hover:border-pink-deep hover:text-pink-deep transition focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
                            Reset
                        </a>
                        <button type="submit" class="min-h-[48px] flex-1 inline-flex items-center justify-center rounded-xl bg-ink text-white text-sm font-medium hover:bg-pink-deep transition focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
                            Tampilkan hasil
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Desktop inline category chips (same shared partial — one source of truth) --}}
        <nav class="hidden lg:flex mb-6 items-center gap-2 flex-wrap" aria-label="Filter kategori">
            @include('catalog._chips')
        </nav>

        {{-- ============ Search + Sort toolbar (desktop only — mobile uses the sheet above).
             Both controls map to real CatalogController params (q, sort). ============ --}}
        <form method="GET" action="{{ $selectedCategory ? route('shop.category', $selectedCategory) : route('shop.index') }}"
            class="hidden lg:flex lg:items-center lg:justify-between gap-3 mb-8">
            <div class="relative w-72">
                <label for="shop-search" class="sr-only">Cari produk</label>
                <input id="shop-search" type="search" name="q" value="{{ request('q') }}" placeholder="Cari hijab…"
                    class="w-full min-h-[44px] pl-9 pr-3 rounded-xl border border-line bg-white text-sm text-ink placeholder:text-muted/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep transition">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if ($sort !== 'newest')
                    <input type="hidden" name="sort" value="{{ $sort }}">
                @endif
            </div>

            <div class="flex items-center gap-3">
                <label for="shop-sort" class="text-xs text-muted whitespace-nowrap">Urutkan</label>
                <select id="shop-sort" name="sort" onchange="this.form.submit()"
                    class="min-h-[44px] px-3 rounded-xl border border-line bg-white text-sm text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep cursor-pointer">
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Harga: Terendah</option>
                    <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Harga: Tertinggi</option>
                    <option value="best_seller" {{ $sort === 'best_seller' ? 'selected' : '' }}>Best Seller</option>
                </select>
                <noscript>
                    <button type="submit" class="min-h-[44px] px-4 rounded-xl bg-ink text-white text-xs font-medium">Terapkan</button>
                </noscript>
            </div>
        </form>

        {{-- ============ Product grid — shared ProductCard, mobile-first 2 → 3 → 4 columns ============ --}}
        @if ($products->count())
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-x-4 gap-y-8 sm:gap-x-6 sm:gap-y-10">
                @foreach ($products as $prod)
                    <x-product-card :product="$prod" />
                @endforeach
            </div>

            @if ($products->hasPages())
                <div class="mt-12">
                    {{-- Branded pagination component — paginator links already carry active filters (withQueryString) --}}
                    <x-pagination :paginator="$products" />
                </div>
            @endif
        @else
            {{-- ============ Tasteful empty state ============ --}}
            <div class="border border-line bg-white py-16 px-6 text-center">
                <p class="font-display text-xl text-ink">Belum ada produk yang cocok</p>
                <p class="text-sm text-muted mt-2 max-w-md mx-auto leading-relaxed">
                    Coba ubah kata kunci pencarian atau pilih kategori lain untuk melihat koleksi MH Scarf selengkapnya.
                </p>
                <a href="{{ route('shop.index') }}"
                    class="mt-6 inline-flex min-h-[44px] items-center px-5 rounded-full bg-pink-deep hover:bg-pink-mauve text-white text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
                    Hapus Filter &amp; Lihat Semua Koleksi
                </a>
            </div>
        @endif
    </main>

    {{-- Tahap 2: mobile filter sheet toggle (vanilla JS, no new dependencies).
         Esc + backdrop + close button dismiss; body scroll locked while open. --}}
    <script>
        (function () {
            const sheet = document.getElementById('shop-filtersheet');
            const backdrop = document.getElementById('shop-filtersheet-backdrop');
            const opener = document.getElementById('shop-open-filtersheet');
            if (!sheet || !backdrop || !opener) return;

            let lastFocused = null;

            function openSheet() {
                lastFocused = document.activeElement;
                sheet.classList.add('is-open');
                backdrop.classList.add('is-open');
                opener.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
                const first = sheet.querySelector('input, select, button, a');
                if (first) first.focus();
            }

            function closeSheet() {
                sheet.classList.remove('is-open');
                backdrop.classList.remove('is-open');
                opener.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
                if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
            }

            opener.addEventListener('click', function () {
                sheet.classList.contains('is-open') ? closeSheet() : openSheet();
            });

            // Any element marked data-shop-close (backdrop, X button) closes the sheet
            document.querySelectorAll('[data-shop-close]').forEach(function (el) {
                el.addEventListener('click', closeSheet);
            });

            // Esc closes the dialog
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && sheet.classList.contains('is-open')) closeSheet();
            });

            // Close the sheet when a category chip inside it navigates (link-based filter)
            sheet.querySelectorAll('a[href*="shop"]').forEach(function (a) {
                a.addEventListener('click', function () {
                    document.body.style.overflow = '';
                });
            });
        })();
    </script>

</x-layouts.app>
