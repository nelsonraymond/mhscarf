<x-layouts.app>
    @section('title', $product->name . ' — MH Scarf')

    @php
        $galleryImages = $product->images;
        $primaryImg = $product->primaryImage()
            ?? $galleryImages->first();

        $defaultVariant = $product->variants
            ->first(fn ($variant) => $variant->is_active && $variant->stock_qty > 0);

        $sizes = $product->variants->pluck('size')->filter()->unique();
        $colors = $product->variants->pluck('color_name')->filter()->unique();

        $ratingSchema = $reviewCount > 0 ? [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($averageRating, 1),
                'reviewCount' => $reviewCount,
            ],
        ] : null;
    @endphp

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 md:py-10 lg:px-8">

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="mb-6 max-w-2xl rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm text-rose-700">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="mb-6 text-[11px] text-muted">
            <ol class="flex flex-wrap items-center gap-1.5">
                <li>
                    <a href="{{ route('home') }}"
                       class="transition hover:text-pink-deep">
                        Beranda
                    </a>
                </li>
                <li aria-hidden="true" class="text-line">/</li>
                <li>
                    <a href="{{ route('shop.index') }}"
                       class="transition hover:text-pink-deep">
                        Koleksi
                    </a>
                </li>
                <li aria-hidden="true" class="text-line">/</li>
                <li>
                    <a href="{{ route('shop.category', $product->category->slug) }}"
                       class="transition hover:text-pink-deep">
                        {{ $product->category->name }}
                    </a>
                </li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page"
                    class="max-w-[40vw] truncate text-ink">
                    {{ $product->name }}
                </li>
            </ol>
        </nav>

        {{-- Product layout --}}
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-2 lg:gap-14">

            {{-- =====================================================
                 PRODUCT GALLERY
            ====================================================== --}}
            <div class="min-w-0 space-y-3 lg:sticky lg:top-24">

                @if ($primaryImg)
                    <div class="relative aspect-[4/5] w-full overflow-hidden rounded-lg bg-cream sm:rounded-xl">
                        <img
                            src="{{ Storage::url($primaryImg->image_path) }}"
                            alt="{{ $primaryImg->alt_text ?? $product->name }}"
                            class="h-full w-full object-cover object-center"
                            id="pd-main-image"
                            fetchpriority="high"
                        >
                    </div>

                    @if ($galleryImages->count() > 1)
                        <div class="grid grid-cols-4 gap-2 sm:gap-3">
                            @foreach ($galleryImages as $img)
                                @php
                                    $isActive = $img->image_path === $primaryImg->image_path;
                                @endphp

                                <button
                                    type="button"
                                    data-src="{{ Storage::url($img->image_path) }}"
                                    data-alt="{{ $img->alt_text ?? $product->name }}"
                                    aria-label="Lihat foto {{ $loop->iteration }}"
                                    aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                                    class="pd-thumb aspect-[4/5] overflow-hidden rounded-lg border bg-cream transition
                                        {{ $isActive
                                            ? 'border-pink-deep'
                                            : 'border-transparent hover:border-pink-mauve' }}"
                                >
                                    <img
                                        src="{{ Storage::url($img->image_path) }}"
                                        alt=""
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    >
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="flex aspect-[4/5] items-center justify-center rounded-xl border border-line bg-gradient-to-b from-pink-soft/40 to-cream">
                        <span class="font-display text-base italic text-pink-mauve">
                            Foto {{ $product->name }} segera hadir
                        </span>
                    </div>
                @endif

            </div>

            {{-- =====================================================
                 PRODUCT INFORMATION
            ====================================================== --}}
            <div class="min-w-0 pt-1 lg:pt-0">
                {{-- Product name --}}
                <h1 class="font-display text-2xl font-medium leading-snug text-ink sm:text-3xl lg:text-4xl">
                    {{ $product->name }}
                </h1>

                {{-- Reviews summary --}}
                @if ($reviewCount > 0)
                    <a href="#ulasan"
                       class="group mt-3 inline-flex items-center gap-2">
                        <x-star-rating :value="$averageRating" class="text-sm" />

                        <span class="text-xs text-muted transition group-hover:text-pink-deep">
                            {{ number_format($averageRating, 1) }}
                            · {{ $reviewCount }} ulasan
                        </span>
                    </a>
                @endif

                {{-- Price --}}
                <div class="mt-4 flex flex-wrap items-center gap-2 sm:mt-5">
                    <span id="pd-price">
                        <x-price-display
                            :price="(float) $product->base_price"
                            :compare-at="(float) $product->compare_at_price"
                            size="lg"
                        />
                    </span>

                    @if ($product->compare_at_price > $product->base_price)
                        @php
                            $discountPct = round(
                                (1 - $product->base_price / $product->compare_at_price) * 100
                            );
                        @endphp

                        <span class="text-[11px] font-semibold text-pink-deep">
                            Hemat {{ $discountPct }}%
                        </span>
                    @endif
                </div>

                {{-- Stock status --}}
                <div id="pd-stock-row" class="mt-4 text-xs" aria-live="polite">
                    @if ($totalStock > 0)
                        <span class="inline-flex items-center gap-2 text-emerald-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            <span id="pd-stock-text">
                                Stok tersedia — {{ $totalStock }} pcs siap kirim
                            </span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2 text-muted">
                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                            <span id="pd-stock-text">Stok sedang habis</span>
                        </span>
                    @endif
                </div>

                {{-- =================================================
                     PURCHASE CONTROLS
                ================================================== --}}
                @if ($product->variants->isNotEmpty() && $totalStock > 0)

                    {{-- Add to cart form --}}
                    <form
                        method="POST"
                        action="{{ route('cart.items.store') }}"
                        id="pd-cart-form"
                        class="mt-6 space-y-5"
                    >
                        @csrf

                        {{-- Variant selector --}}
                        <fieldset>
                            <legend class="mb-2 text-xs font-semibold uppercase tracking-wider text-ink">
                                Pilih Varian
                            </legend>

                            <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3">
                                @foreach ($product->variants as $variant)
                                    @php
                                        $sellable = $variant->is_active && $variant->stock_qty > 0;
                                        $isSelected = $defaultVariant
                                            && $variant->id === $defaultVariant->id;
                                    @endphp

                                    <label
                                        class="relative min-h-11 rounded-xl border p-3 transition
                                            {{ $sellable
                                                ? 'cursor-pointer border-line hover:border-pink-deep has-[:checked]:border-pink-deep has-[:checked]:bg-pink-soft/25 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-pink-deep'
                                                : 'cursor-not-allowed border-line opacity-50' }}"
                                    >
                                        <input
                                            type="radio"
                                            name="product_variant_id"
                                            value="{{ $variant->id }}"
                                            class="pd-variant-radio sr-only"
                                            data-final-price="{{ (int) round($variant->finalPrice()) }}"
                                            data-stock="{{ (int) $variant->stock_qty }}"
                                            data-name="{{ $variant->name }}"
                                            {{ $isSelected ? 'checked' : '' }}
                                            {{ !$sellable ? 'disabled' : '' }}
                                            required
                                        >

                                        <span class="flex min-w-0 items-center gap-2">
                                            @if ($variant->color_hex)
                                                <span
                                                    class="h-3.5 w-3.5 shrink-0 rounded-full border border-line"
                                                    style="background-color: {{ $variant->color_hex }}"
                                                ></span>
                                            @endif

                                            <span class="truncate text-xs font-medium text-ink">
                                                {{ $variant->name }}
                                            </span>
                                        </span>

                                        <span class="mt-1 block text-[10px] text-muted">
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

                        {{-- Quantity --}}
                        <div class="flex items-center gap-4">
                            <div class="flex overflow-hidden rounded-xl border border-line bg-white">
                                <button
                                    type="button"
                                    class="pd-qty-btn h-11 w-11 text-base text-ink transition hover:bg-cream focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-pink-deep"
                                    data-action="dec"
                                    aria-label="Kurangi kuantitas"
                                >−</button>

                                <input
                                    type="number"
                                    name="quantity"
                                    id="pd-quantity"
                                    value="{{ old('quantity', 1) }}"
                                    min="1"
                                    max="{{ $defaultVariant ? $defaultVariant->stock_qty : 1 }}"
                                    required
                                    inputmode="numeric"
                                    class="h-11 w-14 border-x border-line text-center text-sm text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-pink-deep [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none"
                                >

                                <button
                                    type="button"
                                    class="pd-qty-btn h-11 w-11 text-base text-ink transition hover:bg-cream focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-pink-deep"
                                    data-action="inc"
                                    aria-label="Tambah kuantitas"
                                >+</button>
                            </div>

                            <p id="pd-qty-hint"
                               class="text-[11px] text-muted"
                               aria-live="polite">
                                Maksimum sesuai stok varian terpilih.
                            </p>
                        </div>

                        {{-- Add to cart button --}}
                        <div>
                            <x-primary-button
                                type="submit"
                                class="min-h-12 w-full justify-center"
                                id="pd-add-btn"
                                :disabled="!$defaultVariant"
                            >
                                Tambah ke Keranjang
                            </x-primary-button>
                        </div>
                    </form>

                    {{-- Buy now form: separate from cart form --}}
                    <form
                        method="POST"
                        action="{{ route('buy.now') }}"
                        id="pd-buy-now-form"
                        class="mt-3"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="product_variant_id"
                            id="pd-buy-variant"
                            value="{{ $defaultVariant?->id ?? '' }}"
                        >

                        <input
                            type="hidden"
                            name="quantity"
                            id="pd-buy-quantity"
                            value="{{ old('quantity', 1) }}"
                        >

                        <x-primary-button
                            type="submit"
                            class="min-h-12 w-full justify-center"
                            id="pd-buy-now-btn"
                            :disabled="!$defaultVariant"
                        >
                            Beli Sekarang
                        </x-primary-button>
                    </form>

                    {{-- Wishlist --}}
                    <div class="mt-3 flex items-center gap-3">
                        @auth
                            @php
                                $isWishlisted = in_array(
                                    $product->id,
                                    $wishlistedIds
                                );
                            @endphp

                            <form
                                method="POST"
                                action="{{ route('wishlist.toggle', $product) }}"
                                class="inline-flex"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    aria-label="{{ $isWishlisted ? 'Hapus dari wishlist' : 'Tambah ke wishlist' }}"
                                    title="{{ $isWishlisted ? 'Hapus dari wishlist' : 'Tambah ke wishlist' }}"
                                    class="inline-flex h-12 w-12 items-center justify-center rounded-xl border transition
                                        {{ $isWishlisted
                                            ? 'border-pink-deep bg-pink-soft/25 text-pink-deep'
                                            : 'border-line text-pink-deep hover:border-pink-deep hover:bg-pink-soft/30' }}"
                                >
                                    <svg
                                        class="h-5 w-5 {{ $isWishlisted ? 'fill-pink-deep' : 'fill-none' }}"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M12 20s-7-4.6-9.2-9A5.2 5.2 0 0112 6.5 5.2 5.2 0 0121.2 11C19 15.4 12 20 12 20z"
                                        />
                                    </svg>
                                </button>
                            </form>
                        @else
                            <a
                                href="{{ route('login') }}"
                                aria-label="Masuk untuk menyimpan ke wishlist"
                                title="Masuk untuk menyimpan ke wishlist"
                                class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-line text-pink-deep transition hover:border-pink-deep hover:bg-pink-soft/30"
                            >
                                <svg
                                    class="h-5 w-5 fill-none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 20s-7-4.6-9.2-9A5.2 5.2 0 0112 6.5 5.2 5.2 0 0121.2 11C19 15.4 12 20 12 20z"
                                    />
                                </svg>
                            </a>
                        @endauth
                    </div>

                @elseif ($totalStock <= 0)

                    <div class="mt-6">
                        <button
                            type="button"
                            disabled
                            class="min-h-12 w-full cursor-not-allowed rounded-xl bg-gray-100 px-7 text-sm font-medium text-gray-500"
                        >
                            Stok Habis
                        </button>

                        <p class="mt-2 text-[11px] text-muted">
                            Varian akan muncul kembali setelah restock.
                        </p>
                    </div>

                @else

                    <p class="mt-6 rounded-xl border border-line bg-white px-4 py-3 text-xs text-muted">
                        Produk ini belum memiliki varian yang dapat dibeli.
                        Hubungi kami untuk informasi ketersediaan.
                    </p>

                @endif

                {{-- =================================================
                     DESCRIPTION, MATERIAL & CARE
                ================================================== --}}
                @if ($product->description || $product->material || $product->care_instructions)
                    <section class="mt-8 space-y-6 border-t border-line pt-6">

                        @if ($product->description)
                            <div>
                                <h2 class="mb-3 text-sm font-semibold text-ink">
                                    Deskripsi Produk
                                </h2>

                                <div class="whitespace-pre-line text-sm leading-7 text-muted">{{ $product->description }}</div>
                            </div>
                        @endif

                        @if ($product->material)
                            <div>
                                <h3 class="mb-2 text-sm font-semibold text-ink">
                                    Bahan
                                </h3>

                                <p class="text-sm leading-7 text-muted">
                                    {{ $product->material }}
                                </p>
                            </div>
                        @endif

                        @if ($product->care_instructions)
                            <div>
                                <h3 class="mb-2 text-sm font-semibold text-ink">
                                    Perawatan
                                </h3>

                                <p class="whitespace-pre-line text-sm leading-7 text-muted">{{ $product->care_instructions }}</p>
                            </div>
                        @endif

                    </section>
                @endif

                {{-- Available colors and sizes --}}
                @if ($colors->isNotEmpty() || $sizes->isNotEmpty())
                    <dl class="mt-6 space-y-3 border-t border-line pt-6 text-sm">
                        @if ($colors->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                <dt class="shrink-0 text-[11px] uppercase tracking-wider text-muted">
                                    Warna tersedia
                                </dt>
                                <dd class="text-ink">
                                    {{ $colors->implode(', ') }}
                                </dd>
                            </div>
                        @endif

                        @if ($sizes->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                <dt class="shrink-0 text-[11px] uppercase tracking-wider text-muted">
                                    Ukuran
                                </dt>
                                <dd class="text-ink">
                                    {{ $sizes->implode(', ') }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                @endif

            </div>
        </div>

        {{-- =========================================================
             CUSTOMER REVIEWS
        ========================================================== --}}
        <section
            id="ulasan"
            class="mt-14 scroll-mt-24 border-t border-line pt-10 md:mt-20"
            aria-labelledby="ulasan-h"
        >
            <h2 id="ulasan-h"
                class="mb-6 font-display text-xl text-ink md:text-2xl">
                Ulasan Pelanggan
            </h2>

            @if ($reviewCount > 0)
                <div class="grid grid-cols-1 items-start gap-8 md:grid-cols-12">

                    {{-- Rating summary --}}
                    <div class="rounded-2xl border border-line bg-white p-6 shadow-card md:col-span-4">
                        <div class="flex items-end gap-2">
                            <span class="font-display text-5xl font-bold leading-none text-ink">
                                {{ number_format($averageRating, 1) }}
                            </span>
                            <span class="pb-1 text-sm text-muted">dari 5</span>
                        </div>

                        <x-star-rating :value="$averageRating" class="mt-2 text-lg" />

                        <p class="mt-1 text-xs text-muted">
                            {{ $reviewCount }} ulasan
                        </p>

                        <div class="mt-5 space-y-1.5">
                            @foreach ([5, 4, 3, 2, 1] as $star)
                                @php
                                    $count = (int) ($ratingBreakdown[$star] ?? 0);
                                    $pct = $reviewCount
                                        ? round($count / $reviewCount * 100)
                                        : 0;
                                @endphp

                                <div class="flex items-center gap-2 text-xs text-muted">
                                    <span class="w-2.5">{{ $star }}</span>
                                    <span class="text-gold" aria-hidden="true">★</span>

                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-line/70">
                                        <div
                                            class="h-full rounded-full bg-gold"
                                            style="width: {{ $pct }}%"
                                        ></div>
                                    </div>

                                    <span class="w-6 text-right">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Review list --}}
                    <div class="space-y-4 md:col-span-8">
                        @foreach ($reviews as $review)
                            <article class="rounded-2xl border border-line bg-white p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-sm font-semibold text-ink">
                                        {{ $review->maskedName() }}
                                    </p>

                                    <x-star-rating
                                        :value="$review->rating"
                                        class="shrink-0 text-sm"
                                    />
                                </div>

                                @if ($review->comment)
                                    <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-ink/90">{{ $review->comment }}</p>
                                @endif

                                <div class="mt-3 flex items-center gap-2 text-[11px] text-muted">
                                    <time datetime="{{ $review->created_at->toDateString() }}">
                                        {{ $review->created_at->translatedFormat('d M Y') }}
                                    </time>

                                    @if ($review->orderItem?->variant_name)
                                        <span aria-hidden="true">•</span>
                                        <span>
                                            Varian: {{ $review->orderItem->variant_name }}
                                        </span>
                                    @endif
                                </div>
                            </article>
                        @endforeach

                        {{ $reviews->links() }}
                    </div>
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-line p-8 text-center">
                    <p class="text-sm font-medium text-ink">
                        Belum ada ulasan
                    </p>
                    <p class="mt-1 text-xs text-muted">
                        Jadilah yang pertama mengulas setelah pesanan Anda tiba.
                    </p>
                </div>
            @endif
        </section>

        {{-- =========================================================
             RELATED PRODUCTS
        ========================================================== --}}
        @if ($relatedProducts->isNotEmpty())
            <section class="mt-14 border-t border-line pt-10 md:mt-20">
                <div class="mb-6 flex items-end justify-between gap-4">
                    <h2 class="font-display text-xl text-ink md:text-2xl">
                        Lengkapi Gayamu
                    </h2>

                    <a
                        href="{{ route('shop.category', $product->category->slug) }}"
                        class="whitespace-nowrap text-xs text-pink-deep transition hover:text-pink-mauve"
                    >
                        Semua {{ $product->category->name }} →
                    </a>
                </div>

                <div class="grid grid-cols-2 gap-x-4 gap-y-8 sm:gap-x-6 lg:grid-cols-4">
                    @foreach ($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif

    </main>

    {{-- Product structured data --}}
    @if ($ratingSchema)
        <script type="application/ld+json">
            @json($ratingSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        </script>
    @endif

    {{-- =============================================================
         GALLERY AND VARIANT INTERACTIONS
    ============================================================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mainImage = document.getElementById('pd-main-image');
            const thumbnails = document.querySelectorAll('.pd-thumb');
            const radios = document.querySelectorAll('.pd-variant-radio');

            const priceElement = document.getElementById('pd-price');
            const stockText = document.getElementById('pd-stock-text');
            const quantityHint = document.getElementById('pd-qty-hint');
            const quantityInput = document.getElementById('pd-quantity');

            const buyVariantInput = document.getElementById('pd-buy-variant');
            const buyQuantityInput = document.getElementById('pd-buy-quantity');

            const addButton = document.getElementById('pd-add-btn');
            const buyButton = document.getElementById('pd-buy-now-btn');
            const buyForm = document.getElementById('pd-buy-now-form');

            const basePrice = {{ (int) round($product->base_price) }};
            const compareAt = {{ (int) round($product->compare_at_price ?? 0) }};

            const formatPrice = value =>
                'Rp' + Number(value).toLocaleString('id-ID');

            function getSelectedVariant() {
                return document.querySelector('.pd-variant-radio:checked');
            }

            function updatePrice(price) {
                if (!priceElement) return;

                priceElement.replaceChildren();

                const currentPrice = document.createElement('span');
                currentPrice.className =
                    'text-lg font-semibold text-ink whitespace-nowrap';
                currentPrice.textContent = formatPrice(price);

                priceElement.appendChild(currentPrice);

                if (compareAt > price) {
                    const oldPrice = document.createElement('span');
                    oldPrice.className =
                        'ml-2 text-sm text-muted line-through whitespace-nowrap';
                    oldPrice.textContent = formatPrice(compareAt);

                    priceElement.appendChild(oldPrice);
                }
            }

            function updateQuantity(stock) {
                if (!quantityInput) return;

                const maxStock = Math.max(1, stock);
                const currentQuantity =
                    Number.parseInt(quantityInput.value, 10) || 1;

                quantityInput.max = maxStock;
                quantityInput.value = Math.min(
                    maxStock,
                    Math.max(1, currentQuantity)
                );

                if (buyQuantityInput) {
                    buyQuantityInput.value = quantityInput.value;
                }
            }

            function updateVariant(radio) {
                if (!radio) return;

                const price =
                    Number(radio.dataset.finalPrice) || basePrice;

                const stock =
                    Number(radio.dataset.stock) || 0;

                updatePrice(price);

                if (stockText) {
                    stockText.textContent = stock > 0
                        ? 'Stok varian ini: ' + stock + ' pcs'
                        : 'Stok varian ini habis';
                }

                if (quantityHint) {
                    quantityHint.textContent =
                        'Maksimum ' + stock + ' pcs untuk varian ini.';
                }

                if (buyVariantInput) {
                    buyVariantInput.value = radio.value;
                }

                updateQuantity(stock);

                [addButton, buyButton].forEach(button => {
                    if (!button) return;

                    button.disabled = stock < 1;
                    button.classList.toggle('opacity-50', stock < 1);
                    button.classList.toggle('cursor-not-allowed', stock < 1);
                });
            }

            // Switch main gallery image.
            thumbnails.forEach(button => {
                button.addEventListener('click', function () {
                    if (!mainImage) return;

                    mainImage.src = button.dataset.src;
                    mainImage.alt = button.dataset.alt || '';

                    thumbnails.forEach(thumbnail => {
                        const active = thumbnail === button;

                        thumbnail.classList.toggle(
                            'border-pink-deep',
                            active
                        );

                        thumbnail.classList.toggle(
                            'border-transparent',
                            !active
                        );

                        thumbnail.setAttribute(
                            'aria-pressed',
                            String(active)
                        );
                    });
                });
            });

            // Update price and stock when a variant changes.
            radios.forEach(radio => {
                radio.addEventListener('change', function () {
                    updateVariant(radio);
                });
            });

            // Quantity buttons.
            document.querySelectorAll('.pd-qty-btn').forEach(button => {
                button.addEventListener('click', function () {
                    if (!quantityInput) return;

                    const selected = getSelectedVariant();
                    if (!selected) return;

                    const stock = Number(selected.dataset.stock) || 0;
                    if (stock < 1) return;

                    const current =
                        Number.parseInt(quantityInput.value, 10) || 1;

                    const maximum = stock;

                    quantityInput.value =
                        button.dataset.action === 'inc'
                            ? Math.min(maximum, current + 1)
                            : Math.max(1, current - 1);

                    updateQuantity(stock);
                });
            });

            // Keep the quantity fields synchronized.
            if (quantityInput) {
                quantityInput.addEventListener('input', function () {
                    const selected = getSelectedVariant();
                    if (!selected) return;

                    updateQuantity(Number(selected.dataset.stock) || 0);
                });

                quantityInput.addEventListener('change', function () {
                    const selected = getSelectedVariant();
                    if (!selected) return;

                    updateQuantity(Number(selected.dataset.stock) || 0);
                });
            }

            // Validate the selected variant before submitting Buy Now.
            if (buyForm) {
                buyForm.addEventListener('submit', function (event) {
                    const selected = getSelectedVariant();

                    if (
                        !selected ||
                        selected.disabled ||
                        Number(selected.dataset.stock) < 1
                    ) {
                        event.preventDefault();
                        return;
                    }

                    updateVariant(selected);
                });
            }

            // Initialize the interface from the default selected variant.
            const initialVariant = getSelectedVariant();

            if (initialVariant) {
                updateVariant(initialVariant);
            }
        });
    </script>
</x-layouts.app>
