<x-layouts.app>
    @section('title', 'MH Scarf Store — Modern Indonesian Modest Fashion')

    {{-- =========================================================================
         1. ANNOUNCEMENT BAR  ──▶ Rendered via <x-announcement-bar /> in app layout
         2. NAVBAR            ──▶ Rendered via <x-navbar /> in app layout
         ========================================================================= --}}

    {{-- =========================================================================
         3. HERO CAMPAIGN (DESIGN.md v3 §6 & §7.3: Image-led, editorial typography)
         ========================================================================= --}}
    <section id="hero-campaign" class="relative overflow-hidden bg-gradient-to-b from-[#FFFDFB] via-cream to-[#FCF8F9] border-b border-line/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 md:py-20 lg:py-24">
            <div class="grid md:grid-cols-12 gap-8 lg:gap-12 items-center">

                {{-- Left Text Composition (6 cols on lg) --}}
                <div class="md:col-span-7 lg:col-span-6 text-center md:text-left order-2 md:order-1">
                    <p class="text-[11px] tracking-[0.25em] uppercase text-pink-mauve font-semibold mb-3">
                        Koleksi Modest Kontemporer
                    </p>
                    <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-semibold text-ink leading-[1.15] tracking-tight">
                        Elegance in Every <span class="italic font-normal text-pink-mauve">Drape</span>
                    </h1>
                    <p class="mt-4 sm:mt-5 text-sm sm:text-base text-muted leading-relaxed max-w-lg mx-auto md:mx-0">
                        Koleksi hijab dan modest-wear Indonesia yang menggabungkan kelembutan material terbaik, kerapian jahitan, dan keanggunan bersahaja untuk setiap momen harian Anda.
                    </p>

                    <div class="mt-7 sm:mt-8 flex flex-col sm:flex-row items-center gap-3 justify-center md:justify-start">
                        <x-primary-button :href="route('shop.index')" class="w-full sm:w-auto">
                            Belanja Sekarang
                        </x-primary-button>
                        <x-secondary-button href="#koleksi-kategori" class="w-full sm:w-auto">
                            Jelajahi Kategori
                        </x-secondary-button>
                    </div>

                    {{-- Restrained Trust Features (Clean text pills, no sparkles or emojis per §2) --}}
                    <div class="mt-9 sm:mt-10 pt-6 border-t border-line/60 flex flex-wrap items-center justify-center md:justify-start gap-x-6 gap-y-2 text-xs text-muted">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-pink-mauve"></span>
                            Material Pilihan
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-pink-mauve"></span>
                            Pengiriman Cepat
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-pink-mauve"></span>
                            100% Original
                        </span>
                    </div>
                </div>

                {{-- Right Campaign Visual Area (5-6 cols on lg) --}}
                <div class="md:col-span-5 lg:col-span-6 order-1 md:order-2 w-full">
                    <div class="relative max-w-md mx-auto md:max-w-none">
                        <div class="relative aspect-[4/5] rounded-md overflow-hidden bg-[#FAF6F7] border border-line shadow-xs">
                            @php
                                $heroProduct = $featuredProducts->first() ?? $bestSellers->first();
                                $heroImg = $heroProduct?->primaryImage();
                            @endphp

                            @if ($heroImg)
                                <img src="{{ Storage::url($heroImg->image_path) }}" alt="{{ $heroProduct->name }}"
                                    class="w-full h-full object-cover">
                            @else
                                {{-- Tasteful neutral editorial hero panel per §10 --}}
                                <div class="w-full h-full flex flex-col items-center justify-between p-8 sm:p-10 text-center bg-gradient-to-br from-[#FAF5F7] via-[#FFF9F5] to-[#F3EAEE]">
                                    <div class="pt-4">
                                        <p class="text-[10px] tracking-[0.3em] uppercase text-pink-mauve font-semibold">Editorial Series</p>
                                        <p class="font-display italic text-2xl sm:text-3xl text-ink mt-2">The Modern Silhouette</p>
                                    </div>
                                    <div class="py-6 my-auto">
                                        <p class="font-display text-4xl sm:text-5xl font-light text-ink/20 tracking-widest uppercase">MH Scarf</p>
                                        <p class="text-xs text-muted/80 mt-2 font-normal">Kenyamanan & keanggunan sehari-hari</p>
                                    </div>
                                    <div class="w-full pt-4 border-t border-line/60 flex items-center justify-between text-[11px] text-muted">
                                        <span>Voal & Silk Edition</span>
                                        <span class="text-pink-mauve font-medium">Musim Ini</span>
                                    </div>
                                </div>
                            @endif

                            {{-- Editorial Caption Overlay Card --}}
                            <div class="absolute bottom-4 left-4 right-4 sm:right-auto sm:max-w-xs bg-white/95 backdrop-blur-md rounded-md p-3.5 border border-line/80 shadow-xs">
                                <p class="text-[10px] uppercase tracking-[0.2em] text-pink-mauve font-semibold">Sorotan Musim Ini</p>
                                <p class="font-display text-sm font-medium text-ink mt-0.5">
                                    {{ $heroProduct?->name ?? 'Signature Collection' }}
                                </p>
                                <a href="{{ $heroProduct ? route('shop.product', $heroProduct->slug) : route('shop.index') }}"
                                    class="text-xs text-pink-mauve hover:text-[#A56684] font-medium mt-1 inline-flex items-center gap-1">
                                    <span>Lihat Detail</span>
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- =========================================================================
         4. SHOP BY CATEGORY (DESIGN.md v3 §7.4: Editorial category discovery)
         ========================================================================= --}}
    <section id="koleksi-kategori" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 md:py-20">
        <x-section-heading
            eyebrow="Kurasi Gaya"
            title="Kategori Pilihan"
            subtitle="Temukan potongan hijab dan modest-wear yang dirancang sesuai preferensi berbusana Anda."
            :href="route('shop.index')"
            linkLabel="Lihat Semua Kategori"
        />

        {{-- Editorial Grid: Varied layout for genuine fashion aesthetic --}}
        <div class="mt-8 sm:mt-10 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5 lg:gap-6">
            @foreach ($categories->take(8) as $index => $cat)
                <x-category-card :category="$cat" :size="$index === 0 ? 'tall' : 'md'" />
            @endforeach
        </div>
    </section>

    {{-- =========================================================================
         5. BEST SELLERS / MOST LOVED (DESIGN.md v3 §7.5: High-converting curation)
         ========================================================================= --}}
    @if ($bestSellers->isNotEmpty())
        <section id="best-sellers" class="bg-surface/50 border-y border-line/60 py-14 sm:py-16 md:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <x-section-heading
                    eyebrow="Paling Dicari"
                    title="Most Loved"
                    subtitle="Pilihan favorit yang paling sering dipilih untuk menemani hari-harimu."
                    :href="route('shop.index', ['sort' => 'best_seller'])"
                    linkLabel="Semua Best Seller"
                />

                <div class="mt-8 sm:mt-10 grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 lg:gap-6">
                    @foreach ($bestSellers as $prod)
                        <x-product-card :product="$prod" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- =========================================================================
         6. NEW ARRIVALS / JUST IN (DESIGN.md v3 §7.6: Latest catalog drops)
         ========================================================================= --}}
    @if ($newArrivals->isNotEmpty())
        <section id="new-arrivals" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 md:py-20">
            <x-section-heading
                eyebrow="Baru Tiba"
                title="Just In"
                subtitle="Rilisan terbaru dengan sentuhan modern, warna lembut, dan kenyamanan harian."
                :href="route('shop.index', ['sort' => 'newest'])"
                linkLabel="Semua Koleksi Baru"
            />

            <div class="mt-8 sm:mt-10 grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 lg:gap-6">
                @foreach ($newArrivals as $prod)
                    <x-product-card :product="$prod" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- =========================================================================
         7. EDITORIAL CAMPAIGN (DESIGN.md v3 §7.7: Asymmetric fashion storytelling)
         ========================================================================= --}}
    <section id="tentang-kami" class="bg-gradient-to-r from-cream via-[#FDF8F9] to-cream border-y border-line/70 py-14 sm:py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-12 gap-8 lg:gap-14 items-center">

                {{-- Left Image / Lookbook block --}}
                <div class="md:col-span-6 lg:col-span-6">
                    <div class="relative aspect-[4/5] rounded-md overflow-hidden bg-[#FAF6F7] border border-line shadow-xs">
                        @php
                            $campaignImg = $featuredProducts->get(1)?->primaryImage() ?? $featuredProducts->first()?->primaryImage();
                        @endphp
                        @if ($campaignImg)
                            <img src="{{ Storage::url($campaignImg->image_path) }}" alt="Editorial MH Scarf"
                                class="w-full h-full object-cover">
                        @else
                            {{-- Tasteful editorial placeholder --}}
                            <div class="w-full h-full flex flex-col justify-between p-8 sm:p-12 bg-gradient-to-br from-[#FDF9FA] to-[#EFE2E7] text-ink">
                                <div>
                                    <p class="text-[10px] tracking-[0.3em] uppercase text-pink-mauve font-semibold">Kampanye Editorial</p>
                                    <h3 class="font-display italic text-2xl sm:text-3xl font-medium mt-2">A Quiet Kind of Elegance</h3>
                                </div>
                                <div class="my-auto py-8">
                                    <p class="text-xs sm:text-sm text-muted leading-relaxed max-w-sm">
                                        Setiap lipatan dirancang dengan kesadaran akan keindahan yang bersahaja — tidak berlebihan, namun selalu berkesan.
                                    </p>
                                </div>
                                <div class="pt-4 border-t border-line/60 flex items-center justify-between text-xs text-muted">
                                    <span>MH Scarf Studio Collection</span>
                                    <span class="text-pink-mauve font-medium">Modest & Modern</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right Editorial Narrative --}}
                <div class="md:col-span-6 lg:col-span-6 space-y-5 text-center md:text-left">
                    <p class="text-[11px] tracking-[0.25em] uppercase text-pink-mauve font-semibold">Filosofi Desain</p>
                    <h2 class="font-display text-2xl sm:text-3xl lg:text-4xl font-semibold text-ink leading-tight">
                        Keanggunan yang Tenang untuk Momen Sehari-Hari
                    </h2>
                    <p class="text-sm sm:text-base text-muted leading-relaxed">
                        Di MH Scarf Store, kami percaya bahwa hijab bukan sekadar penutup kepala, melainkan perpanjangan dari karakter dan kenyamanan perempuan muslimah modern.
                    </p>
                    <p class="text-sm text-muted leading-relaxed">
                        Kami mengutamakan kurasi bahan bernapas lega, drape kain yang jatuh rapi tanpa perlu usaha ekstra, serta pilihan warna lembut yang melengkapi ragam rona kulit Indonesia.
                    </p>

                    <div class="pt-3">
                        <x-primary-button :href="route('shop.index')" class="inline-flex">
                            Jelajahi Koleksi MH Scarf
                        </x-primary-button>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- =========================================================================
         8. MATERIAL EDUCATION (DESIGN.md v3 §7.8: Fabric craftsmanship guide)
         ========================================================================= --}}
    <section id="material-education" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 md:py-20">
        <x-section-heading
            align="center"
            eyebrow="Edukasi Bahan"
            title="Kenali Karakteristik Material"
            subtitle="Panduan memilih kain hijab yang paling sesuai dengan aktivitas dan kenyamanan Anda."
        />

        <div class="mt-8 sm:mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Card 1: Voal --}}
            <div class="bg-white border border-line rounded-md p-6 hover:border-pink-mauve/60 transition-colors shadow-2xs flex flex-col justify-between">
                <div>
                    <span class="text-[10px] tracking-[0.2em] uppercase text-pink-mauve font-semibold block mb-2">Harian & Formal</span>
                    <h3 class="font-display text-xl font-medium text-ink">Voal Premium</h3>
                    <p class="text-xs sm:text-sm text-muted mt-2.5 leading-relaxed">
                        Serat halus dengan sirkulasi udara optimal. Tegak sempurna di dahi, tidak berdengung di telinga, dan tetap rapi sepanjang hari.
                    </p>
                </div>
                <div class="mt-5 pt-4 border-t border-line/60">
                    <a href="{{ route('shop.index', ['q' => 'Voal']) }}" class="text-xs font-medium text-ink hover:text-pink-mauve transition-colors inline-flex items-center gap-1">
                        <span>Belanja Koleksi Voal</span>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>

            {{-- Card 2: Ceruty --}}
            <div class="bg-white border border-line rounded-md p-6 hover:border-pink-mauve/60 transition-colors shadow-2xs flex flex-col justify-between">
                <div>
                    <span class="text-[10px] tracking-[0.2em] uppercase text-pink-mauve font-semibold block mb-2">Flowy & Anggun</span>
                    <h3 class="font-display text-xl font-medium text-ink">Ceruty Babydoll</h3>
                    <p class="text-xs sm:text-sm text-muted mt-2.5 leading-relaxed">
                        Tekstur pasir lembut berkarakter flowy nan jatuh. Sangat elegan untuk layering pashmina syari dan gaya formal bersahaja.
                    </p>
                </div>
                <div class="mt-5 pt-4 border-t border-line/60">
                    <a href="{{ route('shop.index', ['q' => 'Ceruty']) }}" class="text-xs font-medium text-ink hover:text-pink-mauve transition-colors inline-flex items-center gap-1">
                        <span>Belanja Koleksi Ceruty</span>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>

            {{-- Card 3: Silk / Satin --}}
            <div class="bg-white border border-line rounded-md p-6 hover:border-pink-mauve/60 transition-colors shadow-2xs flex flex-col justify-between sm:col-span-2 lg:col-span-1">
                <div>
                    <span class="text-[10px] tracking-[0.2em] uppercase text-pink-mauve font-semibold block mb-2">Mewah & Berkilau</span>
                    <h3 class="font-display text-xl font-medium text-ink">Silk & Satin Grade A</h3>
                    <p class="text-xs sm:text-sm text-muted mt-2.5 leading-relaxed">
                        Kilau lembut berkelas yang tidak licin berlebihan. Sentuhan sempurna untuk menghadiri resepsi, acara resmi, dan hari raya.
                    </p>
                </div>
                <div class="mt-5 pt-4 border-t border-line/60">
                    <a href="{{ route('shop.index', ['q' => 'Silk']) }}" class="text-xs font-medium text-ink hover:text-pink-mauve transition-colors inline-flex items-center gap-1">
                        <span>Belanja Koleksi Silk</span>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================================
         9. PROMOTION / SALE (DESIGN.md v3 §7.9: Real voucher & campaign banner)
         ========================================================================= --}}
    <section id="promo" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-14 sm:pb-16 md:pb-20">
        @if ($vouchers->isNotEmpty())
            {{-- Display real database vouchers --}}
            <div class="rounded-md border border-line bg-gradient-to-r from-cream via-[#FFF5F7] to-cream p-6 sm:p-10 md:p-12">
                <div class="max-w-xl">
                    <p class="text-[11px] tracking-[0.2em] uppercase text-pink-mauve font-semibold">Penawaran Eksklusif</p>
                    <h2 class="font-display text-2xl sm:text-3xl md:text-4xl font-semibold text-ink mt-2 leading-tight">
                        Gunakan Kode Promo Pilihan
                    </h2>
                    <p class="text-xs sm:text-sm text-muted mt-2.5 leading-relaxed">
                        Potongan harga langsung saat menyelesaikan checkout untuk pesanan Anda.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-4">
                        @foreach ($vouchers as $v)
                            <div class="bg-white border border-line rounded-md px-4 py-3 flex items-center justify-between gap-4 shadow-2xs">
                                <div>
                                    <p class="font-mono text-xs font-semibold text-pink-mauve tracking-widest">{{ $v->code }}</p>
                                    <p class="text-[11px] text-muted">
                                        @if ($v->type === 'percentage')
                                            Diskon {{ (int) $v->value }}%
                                        @elseif ($v->type === 'free_shipping')
                                            Gratis Ongkir
                                        @else
                                            Potongan Rp{{ number_format($v->value, 0, ',', '.') }}
                                        @endif
                                    </p>
                                </div>
                                <button type="button"
                                    data-copy-code="{{ $v->code }}"
                                    class="js-copy-code text-xs text-ink hover:text-pink-mauve font-medium border border-line rounded-sm px-2.5 py-1 hover:border-pink-mauve transition-colors cursor-pointer">
                                    Salin
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            {{-- Tasteful minimal promo block linking to catalog sale/promotions --}}
            <div class="rounded-md border border-line bg-gradient-to-r from-cream via-[#FDF6F8] to-cream p-6 sm:p-10 md:p-12 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="max-w-xl">
                    <p class="text-[11px] tracking-[0.2em] uppercase text-pink-mauve font-semibold">Program Belanja MH Scarf</p>
                    <h2 class="font-display text-2xl sm:text-3xl font-semibold text-ink mt-1.5 leading-tight">
                        Gratis Ongkir & Penawaran Spesial
                    </h2>
                    <p class="text-xs sm:text-sm text-muted mt-2 leading-relaxed">
                        Nikmati layanan pengiriman aman dan bebas biaya kirim dengan minimum belanja ke seluruh wilayah Indonesia.
                    </p>
                </div>
                <div class="shrink-0">
                    <x-primary-button :href="route('shop.index')">
                        Mulai Belanja
                    </x-primary-button>
                </div>
            </div>
        @endif
    </section>

    {{-- =========================================================================
         10. CUSTOMER REVIEWS (DESIGN.md v3 §7.10: Real published reviews)
         ========================================================================= --}}
    <section id="customer-reviews" class="bg-surface/50 border-y border-line/60 py-14 sm:py-16 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-section-heading
                align="center"
                eyebrow="Kepuasan Pelanggan"
                title="Kata Sahabat MH Scarf"
                subtitle="Ulasan nyata dari pelanggan yang telah mengenakan koleksi hijab kami."
            />

            @if ($reviews->isNotEmpty())
                {{-- Display genuine published reviews from database --}}
                <div class="mt-8 sm:mt-12 grid md:grid-cols-3 gap-6">
                    @foreach ($reviews as $rev)
                        <div class="bg-white border border-line rounded-md p-6 shadow-2xs flex flex-col justify-between">
                            <div>
                                <div class="text-gold text-xs tracking-wider" aria-label="Rating {{ $rev->rating }} bintang">
                                    {{ str_repeat('★', (int) $rev->rating) }}{{ str_repeat('☆', max(0, 5 - (int) $rev->rating)) }}
                                </div>
                                @if ($rev->title)
                                    <h4 class="font-medium text-sm text-ink mt-2">{{ $rev->title }}</h4>
                                @endif
                                <p class="text-xs sm:text-sm text-muted mt-2 leading-relaxed italic">
                                    “{{ $rev->comment }}”
                                </p>
                            </div>
                            <div class="mt-5 pt-3.5 border-t border-line/60 flex items-center justify-between text-xs text-muted">
                                <div>
                                    <p class="font-medium text-ink">{{ $rev->user?->name ?? 'Pelanggan MH Scarf' }}</p>
                                    <p class="text-[11px] text-muted">{{ $rev->product?->name }}</p>
                                </div>
                                @if ($rev->is_verified_purchase)
                                    <span class="text-[10px] text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-xs font-medium">
                                        Terverifikasi
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Graceful empty state: NO fake testimonials per §2 & §12 --}}
                <div class="mt-8 sm:mt-10 max-w-lg mx-auto text-center p-8 bg-white border border-line rounded-md shadow-2xs">
                    <p class="font-display italic text-base sm:text-lg text-ink">
                        Komitmen kami adalah kepuasan setiap pelanggan.
                    </p>
                    <p class="text-xs text-muted mt-2 leading-relaxed">
                        Setiap pesanan hijab dikurasi dengan teliti dan dikemas higienis. Sampaikan ulasan jujur Anda setelah paket tiba.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('shop.index') }}" class="text-xs font-medium text-pink-mauve hover:text-[#A56684] inline-flex items-center gap-1">
                            <span>Jelajahi Koleksi Kami</span>
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- =========================================================================
         11. JOURNAL / STYLE GUIDE (DESIGN.md v3 §7.11: Visual styling guide placeholder)
         ========================================================================= --}}
    <section id="style-journal" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 md:py-20">
        <x-section-heading
            eyebrow="Inspirasi Gaya"
            title="Style Guide & Lookbook"
            subtitle="Inspirasi padu padan warna dan tekstur untuk beragam agenda formal maupun santai."
            :href="route('shop.index')"
            linkLabel="Lihat Seluruh Koleksi"
        />

        <div class="mt-8 sm:mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Look 1 --}}
            <article class="group bg-white border border-line rounded-md overflow-hidden hover:border-pink-mauve/60 transition-all duration-300">
                <div class="aspect-[16/10] bg-[#FAF6F7] flex flex-col items-center justify-center p-6 text-center">
                    <span class="text-[10px] tracking-[0.25em] uppercase text-pink-mauve font-semibold">Style Guide 01</span>
                    <span class="font-display text-lg text-ink mt-1 font-medium">Daily Minimalist Wrap</span>
                    <span class="text-xs text-muted mt-1">Paduan Voal Ultrafine & Busana Kasual</span>
                </div>
                <div class="p-5">
                    <p class="text-xs text-muted leading-relaxed">
                        Teknik lilit praktis tanpa lipatan rumit. Cocok untuk kuliah, rapat kerja pagi, atau aktivitas dinamis di luar ruangan.
                    </p>
                    <a href="{{ route('shop.index', ['q' => 'Voal']) }}" class="mt-4 inline-flex items-center text-xs font-medium text-pink-mauve hover:text-[#A56684] transition-colors gap-1">
                        <span>Lihat Produk Terkait</span>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </article>

            {{-- Look 2 --}}
            <article class="group bg-white border border-line rounded-md overflow-hidden hover:border-pink-mauve/60 transition-all duration-300">
                <div class="aspect-[16/10] bg-[#F9F5F8] flex flex-col items-center justify-center p-6 text-center">
                    <span class="text-[10px] tracking-[0.25em] uppercase text-pink-mauve font-semibold">Style Guide 02</span>
                    <span class="font-display text-lg text-ink mt-1 font-medium">Pastel & Earthy Harmony</span>
                    <span class="text-xs text-muted mt-1">Palet Rose Blush, Cream, dan Sage</span>
                </div>
                <div class="p-5">
                    <p class="text-xs text-muted leading-relaxed">
                        Menyelaraskan tone hangat kulit Indonesia dengan spektrum warna pastel yang tenang dan menyegarkan penampilan.
                    </p>
                    <a href="{{ route('shop.index') }}" class="mt-4 inline-flex items-center text-xs font-medium text-pink-mauve hover:text-[#A56684] transition-colors gap-1">
                        <span>Lihat Produk Terkait</span>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </article>

            {{-- Look 3 --}}
            <article class="group bg-white border border-line rounded-md overflow-hidden hover:border-pink-mauve/60 transition-all duration-300 sm:col-span-2 lg:col-span-1">
                <div class="aspect-[16/10] bg-[#FAF6F7] flex flex-col items-center justify-center p-6 text-center">
                    <span class="text-[10px] tracking-[0.25em] uppercase text-pink-mauve font-semibold">Style Guide 03</span>
                    <span class="font-display text-lg text-ink mt-1 font-medium">The Evening Elegance</span>
                    <span class="text-xs text-muted mt-1">Sentuhan Silk Satin & Aksesori Halus</span>
                </div>
                <div class="p-5">
                    <p class="text-xs text-muted leading-relaxed">
                        Kilau anggun silk satin berpadu dengan gaun malam atau gamis formal. Memberikan siluet rapi dan drapery yang mewah.
                    </p>
                    <a href="{{ route('shop.index', ['q' => 'Silk']) }}" class="mt-4 inline-flex items-center text-xs font-medium text-pink-mauve hover:text-[#A56684] transition-colors gap-1">
                        <span>Lihat Produk Terkait</span>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </article>
        </div>
    </section>

    {{-- =========================================================================
         12. NEWSLETTER (DESIGN.md v3 §7.12: Typography-led community invitation)
         ========================================================================= --}}
    <x-newsletter-section id="newsletter" />

    {{-- =========================================================================
         13. SERVICE INFO & FAQ (anchors referenced by the footer — real content,
             no dead links). Compact editorial strip + three honest answers.
         ========================================================================= --}}
    <section id="layanan" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-14 sm:pb-16 md:pb-20">
        <div class="grid md:grid-cols-3 gap-6 border-t border-line/70 pt-10 mt-2">
            @php
                $faqItems = [
                    ['id' => 'pengiriman', 'title' => 'Pengiriman',
                        'body' => 'Pesanan diproses dan dikirim setiap hari kerja (Senin–Sabtu) melalui kurir terpercaya ke seluruh Indonesia. Gratis ongkir untuk pesanan mulai Rp75.000. Nomor lacak dibagikan otomatis setelah pesanan diberangkatkan.'],
                    ['id' => 'pengembalian', 'title' => 'Pengembalian & Penukaran',
                        'body' => 'Produk belum digunakan, masih berlabel, dan dalam kemasan asli dapat dikembalikan dalam 7 hari setelah barang diterima. Hubungi tim kami untuk arrange penukaran warna atau ukuran.'],
                    ['id' => 'faq', 'title' => 'Pertanyaan Umum',
                        'body' => 'Ukuran dan warna nyata dapat sedikit berbeda karena pencahayaan layar — detail bahan tercantum pada setiap halaman produk. Pembayaran diproses aman saat checkout, dan status pesanan dapat dipantau kapan saja di halaman Pesanan Saya.'],
                ];
            @endphp
            @foreach ($faqItems as $item)
                <div id="{{ $item['id'] }}" class="bg-white border border-line rounded-md p-6 shadow-2xs scroll-mt-24">
                    <p class="font-display text-base font-medium text-ink">{{ $item['title'] }}</p>
                    <p class="mt-3 text-xs sm:text-sm text-muted leading-relaxed">{{ $item['body'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- =========================================================================
         14. FOOTER           ──▶ Rendered via <x-footer /> in app layout
         ========================================================================= --}}

    <script>
        // Voucher code copy-to-clipboard with a graceful fallback for older browsers.
        (function () {
            document.querySelectorAll('.js-copy-code').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const code = this.dataset.copyCode || '';
                    const done = () => {
                        this.textContent = 'Tersalin';
                        setTimeout(() => { this.textContent = 'Salin'; }, 2000);
                    };
                    if (navigator.clipboard?.writeText) {
                        navigator.clipboard.writeText(code).then(done.bind(this));
                    } else {
                        const input = document.createElement('textarea');
                        input.value = code;
                        document.body.appendChild(input);
                        input.select();
                        document.execCommand('copy');
                        document.body.removeChild(input);
                        done.call(this);
                    }
                });
            });
        })();
    </script>
</x-layouts.app>
