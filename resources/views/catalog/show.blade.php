<x-layouts.app>
    @section('title', $product->name . ' — MH Scarf Store')

    {{-- ============ PRODUCT DETAIL — DESIGN.md v3: product-first, editorial-minimal.
         Reuses: layouts.app (navbar/footer/flash), wishlist-button, price-display, primary/secondary buttons,
         product-card for related items. Cart form posts to existing cart.items.store contract
         (product_variant_id + quantity only; price & stock validated server-side by CartService). ============ --}}

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-10">

        {{-- Validation feedback from cart submission (variant missing / stock insufficient) --}}
        @if ($errors->any())
            <div class="mb-6 max-w-2xl bg-white border border-rose-200 rounded-xl px-4 py-3 text-sm text-rose-700">
                <ul class="space-y-1 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 1. Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="text-[11px] text-muted mb-6">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-pink-deep transition">Beranda</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li><a href="{{ route('shop.index') }}" class="hover:text-pink-deep transition">Koleksi</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li>
                    <a href="{{ route('shop.category', $product->category->slug) }}" class="hover:text-pink-deep transition">
                        {{ $product->category->name }}
                    </a>
                </li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page" class="text-ink max-w-[40vw] truncate">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-14 items-start">

            {{-- ============ 2. Image gallery — genuine records only, 4:5 containers ============ --}}
            @php $galleryImages = $product->images; $primaryImg = $product->primaryImage(); @endphp

            @if ($galleryImages->count() > 1)
                <div class="lg:sticky lg:top-24 space-y-3">
                    <div class="aspect-4/5 rounded-2xl overflow-hidden bg-white border border-line">
                        <img src="{{ Storage::url($primaryImg?->image_path ?? $galleryImages->first()->image_path) }}"
                            alt="{{ $primaryImg?->alt_text ?? $product->name }}"
                            class="w-full h-full object-cover" id="pd-main-image">
                    </div>
                    <div class="flex gap-2.5 overflow-x-auto pb-1">
                        @foreach ($galleryImages as $i => $img)
                            <button type="button" data-index="{{ $i }}"
                                data-src="{{ Storage::url($img->image_path) }}"
                                class="pd-thumb w-16 sm:w-20 aspect-4/5 rounded-lg overflow-hidden border shrink-0 bg-cream transition
                                    {{ $i === 0 ? 'border-pink-deep' : 'border-line hover:border-pink-mauve' }}"
                                aria-label="Lihat gambar {{ $i + 1 }}">
                                <img src="{{ Storage::url($img->image_path) }}" alt="" class="w-full h-full object-cover" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                </div>
            @elseif ($primaryImg)
                <div class="lg:sticky lg:top-24">
                    <div class="aspect-4/5 rounded-2xl overflow-hidden bg-white border border-line">
                        <img src="{{ Storage::url($primaryImg->image_path) }}"
                            alt="{{ $primaryImg->alt_text ?? $product->name }}"
                            class="w-full h-full object-cover">
                    </div>
                </div>
            @else
                {{-- Tasteful fallback — no fake photography, clearly a placeholder --}}
                <div class="lg:sticky lg:top-24">
                    <div class="aspect-4/5 rounded-2xl overflow-hidden bg-gradient-to-b from-pink-soft/40 to-cream border border-line flex items-center justify-center">
                        <span class="font-display italic text-base text-pink-mauve/70">Foto {{ $product->name }} segera hadir</span>
                    </div>
                </div>
            @endif

            {{-- ============ 3–15. Product information & purchase controls ============ --}}
            <div class="min-w-0">

                {{-- Category context + badges --}}
                <div class="flex items-center gap-2 flex-wrap mb-3">
                    <a href="{{ route('shop.category', $product->category->slug) }}"
                        class="text-[11px] tracking-[0.2em] uppercase text-pink-deep font-medium hover:text-pink-mauve transition">
                        {{ $product->category->name }}
                    </a>
                    @if ($product->is_best_seller)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-pink-deep text-white">Best Seller</span>
                    @endif
                    @if ($product->is_featured)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gold text-white">Pilihan</span>
                    @endif
                </div>

                {{-- Name --}}
                <h1 class="font-display text-2xl sm:text-3xl lg:text-4xl font-medium text-ink leading-tight">
                    {{ $product->name }}
                </h1>

                {{-- Real reviews summary only when published reviews exist --}}
                @if ($reviewCount > 0)
                    <div class="flex items-center gap-2 mt-2.5">
                        <div class="flex text-gold text-xs" aria-hidden="true">
                            @for ($i = 1; $i <= 5; $i++)
                                <span>{{ $i <= round($averageRating) ? '★' : '☆' }}</span>
                            @endfor
                        </div>
                        <span class="text-xs text-muted">{{ number_format($averageRating, 1) }} · {{ $reviewCount }} ulasan</span>
                    </div>
                @endif

                {{-- Price (base; final variant price shown live via JS from real additional_price data).
                     id="pd-price" lets the script swap in the selected variant's real finalPrice(). --}}
                <div class="mt-5">
                    <span id="pd-price"><x-price-display :price="(float) $product->base_price" :compare-at="(float) $product->compare_at_price" size="lg" /></span>
                    @if ($product->compare_at_price && $product->compare_at_price > $product->base_price)
                        @php $discountPct = round((1 - $product->base_price / $product->compare_at_price) * 100); @endphp
                        <span class="ml-2 text-[11px] font-semibold text-pink-deep align-middle">Hemat {{ $discountPct }}%</span>
                    @endif
                    <p class="text-[11px] text-muted mt-1" id="pd-variant-price-note">Harga dapat berbeda per varian.</p>
                </div>

                {{-- Stock availability — updates per selected variant via JS (data-stock is real DB data) --}}
                <div class="mt-4 flex items-center gap-2 text-xs" id="pd-stock-row">
                    @if ($totalStock > 0)
                        <span class="inline-flex items-center gap-1.5 text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span id="pd-stock-text">Stok Tersedia — {{ $totalStock }} pcs siap kirim</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-muted">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            Stok sedang habis
                        </span>
                    @endif
                </div>

                
{{-- ============ Add to cart form ============ --}}
@if ($product->variants->isNotEmpty() && $totalStock > 0)
    <form method="POST"
        action="{{ route('cart.items.store') }}"
        class="mt-6 space-y-5"
        id="pd-cart-form">
        @csrf

        {{-- Variant selector --}}
        <fieldset>
            <legend class="text-xs font-semibold uppercase tracking-wider text-ink mb-2">
                Varian
            </legend>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                @foreach ($product->variants as $index => $variant)
                    @php $sellable = $variant->stock_qty > 0; @endphp

                    <label class="relative rounded-xl border p-3 text-left transition min-h-[44px]
                        {{ $sellable
                            ? 'cursor-pointer border-line hover:border-pink-deep has-[:checked]:border-pink-deep has-[:checked]:bg-pink-soft/25 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-pink-deep'
                            : 'opacity-45 cursor-not-allowed border-line' }}">

                        <input type="radio"
                            name="product_variant_id"
                            value="{{ $variant->id }}"
                            class="sr-only pd-variant-radio"
                            data-final-price="{{ (int) round($variant->finalPrice()) }}"
                            data-stock="{{ (int) $variant->stock_qty }}"
                            data-name="{{ $variant->name }}"
                            {{ $index === 0 && $sellable ? 'checked' : '' }}
                            {{ $sellable ? '' : 'disabled' }}>

                        <span class="flex items-center gap-2 min-w-0">
                            @if ($variant->color_hex)
                                <span class="w-3.5 h-3.5 rounded-full border border-line shrink-0"
                                    style="background-color: {{ $variant->color_hex }}"></span>
                            @endif

                            <span class="text-xs font-medium text-ink truncate">
                                {{ $variant->name }}
                            </span>
                        </span>

                        <span class="block text-[10px] text-muted mt-1">
                            {{ $variant->size ?? 'All Size' }}

                            @if ($variant->additional_price > 0)
                                · +Rp{{ number_format($variant->additional_price, 0, ',', '.') }}
                            @endif

                            @unless ($sellable)
                                · Habis
                            @endunless
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        {{-- Quantity selector --}}
        <div class="flex items-center gap-4">
            <div class="flex items-center border border-line rounded-xl overflow-hidden bg-white">
                <button type="button"
                    class="pd-qty-btn w-11 h-11 text-ink hover:bg-cream text-base focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-pink-deep transition"
                    data-action="dec"
                    aria-label="Kurangi kuantitas">−</button>

                <input type="number"
                    name="quantity"
                    id="pd-quantity"
                    value="1"
                    min="1"
                    max="{{ max(1, $product->variants->where('is_active', true)->max('stock_qty')) }}"
                    class="w-14 h-11 text-center text-sm text-ink border-x border-line focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-pink-deep [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none">

                <button type="button"
                    class="pd-qty-btn w-11 h-11 text-ink hover:bg-cream text-base focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-pink-deep transition"
                    data-action="inc"
                    aria-label="Tambah kuantitas">+</button>
            </div>

            <p class="text-[11px] text-muted" id="pd-qty-hint" aria-live="polite">
                Maksimum sesuai stok varian terpilih.
            </p>
        </div>

        {{-- Cart button only: no nested Wishlist form --}}
        <div class="pt-1 flex space-x-2">
            <x-primary-button class="w-full sm:w-auto sm:min-w-[220px]" id="pd-add-btn">
                Tambah ke Keranjang
            </x-primary-button>
            <form method="POST" action="{{ route('buy.now') }}" class="w-full sm:w-auto">
                @csrf
                <input type="hidden" name="product_variant_id" value="{{ $variant->id ?? '' }}">
                <input type="hidden" name="quantity" value="1">
                <x-primary-button type="submit" class="w-full sm:w-auto sm:min-w-[220px]" id="pd-buy-now-btn">
                    Beli Sekarang
                </x-primary-button>
            </form>
        </div>
    </form>

    {{-- Wishlist is a separate form, outside the cart form --}}
    <div class="mt-3 flex items-center gap-3">
        @auth
            @php $isWishlisted = in_array($product->id, $wishlistedIds); @endphp

            <form method="POST"
                action="{{ route('wishlist.toggle', $product) }}"
                class="inline-flex"
                data-wishlist-form>
                @csrf

                <button type="submit"
                    data-filled="{{ $isWishlisted ? '1' : '0' }}"
                    aria-label="{{ $isWishlisted ? 'Hapus dari wishlist' : 'Tambah ke wishlist' }}"
                    title="{{ $isWishlisted ? 'Hapus dari wishlist' : 'Tambah ke wishlist' }}"
                    class="h-12 w-12 inline-flex items-center justify-center rounded-xl border transition cursor-pointer
                        {{ $isWishlisted
                            ? 'border-pink-deep bg-pink-soft/25 text-pink-deep'
                            : 'border-line text-pink-deep hover:border-pink-deep hover:bg-pink-soft/30' }}">

                    <svg class="w-5 h-5 {{ $isWishlisted ? 'fill-pink-deep' : 'fill-none' }} stroke-current"
                        stroke-width="1.7"
                        viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 20s-7-4.6-9.2-9A5.2 5.2 0 0112 6.5 5.2 5.2 0 0121.2 11C19 15.4 12 20 12 20z"/>
                    </svg>
                </button>
            </form>
        @else
            <a href="{{ route('login') }}"
                title="Masuk untuk menyimpan ke wishlist"
                aria-label="Masuk untuk menyimpan ke wishlist"
                class="h-12 w-12 inline-flex items-center justify-center rounded-xl border border-pink-deep text-pink-deep hover:bg-pink-soft/30 transition">

                <svg class="w-5 h-5 fill-none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 20s-7-4.6-9.2-9A5.2 5.2 0 0112 6.5 5.2 5.2 0 0121.2 11C19 15.4 12 20 12 20z"/>
                </svg>
            </a>
        @endauth
    </div>

@elseif ($totalStock <= 0)
    <div class="mt-6">
        <button type="button"
            disabled
            class="w-full sm:w-auto sm:min-w-[220px] min-h-[48px] inline-flex items-center justify-center px-7 rounded-xl bg-gray-100 text-gray-500 text-sm font-medium cursor-not-allowed">
            Stok Habis
        </button>

        <p class="text-[11px] text-muted mt-2">
            Varian akan muncul kembali setelah restock.
        </p>
    </div>
@else
    <p class="mt-6 text-xs text-muted bg-white border border-line rounded-xl px-4 py-3">
        Produk ini belum memiliki varian yang dapat dibeli. Hubungi kami untuk informasi ketersediaan.
    </p>
@endif


                {{-- Description --}}
                @if ($product->description)
                    <div class="mt-8 pt-6 border-t border-line">
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-ink mb-2">Deskripsi</h2>
                        <p class="text-sm text-muted leading-relaxed whitespace-pre-line">{{ $product->description }}</p>
                    </div>
                @endif

                {{-- Material & care — factual fields only, rendered only when populated --}}
                @if ($product->material || $product->care_instructions)
                    <dl class="mt-6 pt-6 border-t border-line grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                        @if ($product->material)
                            <div>
                                <dt class="text-[11px] uppercase tracking-wider text-muted mb-0.5">Bahan</dt>
                                <dd class="text-ink">{{ $product->material }}</dd>
                            </div>
                        @endif
                        @if ($product->care_instructions)
                            <div>
                                <dt class="text-[11px] uppercase tracking-wider text-muted mb-0.5">Perawatan</dt>
                                <dd class="text-ink">{{ $product->care_instructions }}</dd>
                            </div>
                        @endif
                    </dl>
                @endif

                {{-- Minimal specs — only values that genuinely exist in loaded variant data --}}
                @php
                    $sizes = $product->variants->pluck('size')->filter()->unique();
                    $colors = $product->variants->pluck('color_name')->filter()->unique();
                @endphp
                @if ($sizes->isNotEmpty() || $colors->isNotEmpty())
                    <dl class="mt-6 pt-6 border-t border-line space-y-2 text-sm">
                        @if ($colors->isNotEmpty())
                            <div class="flex gap-2"><dt class="text-[11px] uppercase tracking-wider text-muted shrink-0 pt-1">Warna tersedia</dt><dd class="text-ink">{{ $colors->implode(', ') }}</dd></div>
                        @endif
                        @if ($sizes->isNotEmpty())
                            <div class="flex gap-2"><dt class="text-[11px] uppercase tracking-wider text-muted shrink-0 pt-1">Ukuran</dt><dd class="text-ink">{{ $sizes->implode(', ') }}</dd></div>
                        @endif
                    </dl>
                @endif

                {{-- Published reviews — real data only; honest empty state otherwise --}}
                @if ($reviewCount > 0)
                    <div class="mt-8 pt-6 border-t border-line">
                        <h2 class="font-display text-lg text-ink mb-4">Ulasan Pelanggan</h2>
                        <div class="space-y-4">
                            @foreach ($product->reviews->take(3) as $review)
                                <article class="bg-white border border-line rounded-xl p-4">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-medium text-ink">{{ $review->user->name ?? 'Pelanggan' }}</span>
                                        <span class="text-xs text-gold" aria-label="Rating {{ $review->rating }} dari 5">
                                            {{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', 5 - (int) $review->rating) }}
                                        </span>
                                    </div>
                                    @if ($review->comment)
                                        <p class="text-xs text-muted mt-2 leading-relaxed">{{ $review->comment }}</p>
                                    @endif
                                    <time class="block text-[10px] text-muted/70 mt-2">{{ $review->created_at->translatedFormat('d M Y') }}</time>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ Related products — real same-category records only ============ --}}
        @if ($relatedProducts->isNotEmpty())
            <section class="mt-14 md:mt-20 pt-10 border-t border-line">
                <div class="flex items-end justify-between mb-6">
                    <h2 class="font-display text-xl md:text-2xl text-ink">Lengkapi Gayamu</h2>
                    <a href="{{ route('shop.category', $product->category->slug) }}" class="text-xs text-pink-deep hover:text-pink-mauve transition whitespace-nowrap">
                        Semua {{ $product->category->name }} →
                    </a>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-8 sm:gap-x-6">
                    @foreach ($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    {{-- Gallery + variant interaction — progressive enhancement over server-rendered defaults.
         Uses only data already present in the HTML (data-src, data-final-price, data-stock,
         data-name). Server-side validation (CartService) remains the source of truth; this
         layer keeps price/stock/qty UI in sync with the selected variant for clarity. --}}
    <script>
        (function () {
            var main = document.getElementById('pd-main-image');
            document.querySelectorAll('.pd-thumb').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!main) return;
                    main.src = btn.getAttribute('data-src');
                    document.querySelectorAll('.pd-thumb').forEach(function (b) {
                        b.classList.remove('border-pink-deep');
                        b.classList.add('border-line');
                    });
                    btn.classList.add('border-pink-deep');
                    btn.classList.remove('border-line');
                });
            });

            var priceEl = document.getElementById('pd-price');
            var note = document.getElementById('pd-variant-price-note');
            var stockText = document.getElementById('pd-stock-text');
            var qtyHint = document.getElementById('pd-qty-hint');
            var addBtn = document.getElementById('pd-add-btn');
            var qty = document.getElementById('pd-quantity');
            var compareAt = {{ (int) round($product->compare_at_price ?? 0) }};
            var basePrice = {{ (int) round($product->base_price) }};
            function fmt(n) { return 'Rp' + Number(n).toLocaleString('id-ID'); }

            function renderPrice(price) {
                if (!priceEl) return;
                var html = '<span class="text-lg font-semibold text-ink whitespace-nowrap">' + fmt(price) + '</span>';
                if (compareAt > price) {
                    html += ' <span class="text-sm text-muted line-through whitespace-nowrap">' + fmt(compareAt) + '</span>';
                }
                priceEl.innerHTML = html;
            }

            function syncVariant(radio) {
                var price = parseInt(radio.getAttribute('data-final-price'), 10) || basePrice;
                var stock = Math.max(1, parseInt(radio.getAttribute('data-stock'), 10) || 1);
                var name = radio.getAttribute('data-name') || '';

                renderPrice(price);
                if (note) note.textContent = 'Harga varian "' + name + '": ' + fmt(price) + '.';
                if (qty) {
                    qty.max = stock;
                    if ((parseInt(qty.value, 10) || 1) > stock) qty.value = stock;
                }
                if (qtyHint) qtyHint.textContent = 'Maks. ' + stock + ' pcs untuk varian ini.';
                if (stockText) stockText.textContent = 'Stok varian ini: ' + stock + ' pcs';
                if (addBtn) {
                    addBtn.disabled = false;
                    addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }

            document.querySelectorAll('.pd-variant-radio').forEach(function (radio) {
                radio.addEventListener('change', function () { syncVariant(radio); });
            });

            // Initialize display from the server-checked default variant (if any)
            var initial = document.querySelector('.pd-variant-radio:checked');
            if (initial) syncVariant(initial);

            // Clamp manual typing to [1, max] on blur
            if (qty) {
                qty.addEventListener('blur', function () {
                    var v = parseInt(qty.value, 10);
                    var max = parseInt(qty.max, 10) || 1;
                    qty.value = isNaN(v) ? 1 : Math.min(max, Math.max(1, v));
                });
            }

            document.querySelectorAll('.pd-qty-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!qty) return;
                    var v = parseInt(qty.value, 10) || 1;
                    var max = parseInt(qty.max, 10) || 1;
                    qty.value = btn.dataset.action === 'inc' ? Math.min(max, v + 1) : Math.max(1, v - 1);
                });
            });
        })();
    </script>
</x-layouts.app>
