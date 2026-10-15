<x-layouts.app>
    @section('title', 'MH Scarf Store — Modern Indonesian Modest Fashion')

    {{-- =========================================================================
         1. ANNOUNCEMENT BAR  ──▶ Rendered via <x-announcement-bar /> in app layout
         2. NAVBAR            ──▶ Rendered via <x-navbar /> in app layout
         ========================================================================= --}}

    {{-- 3. HERO SLIDER: 3 gambar full-bleed (gaya lozy.id) --}}
@php
    $heroSlides = [
        [
            'file'    => 'images/hero/hero-1.png',
            'mobile'  => 'images/hero/hero-1-mobile.png',
            'eyebrow' => 'Koleksi Baru',
            'title'   => 'Voal Pastel',
            'accent'  => 'Ringan & Rapi',
            'cta'     => 'Lihat Koleksi',
            'url'     => route('shop.index', ['sort' => 'newest']),
        ],
        [
            'file'    => 'images/hero/hero-2.png',
            'mobile'  => 'images/hero/hero-2-mobile.png',
            'eyebrow' => 'Promo',
            'title'   => 'Gratis Ongkir',
            'accent'  => 'Mulai Rp75.000',
            'cta'     => 'Belanja Sekarang',
            'url'     => route('shop.index'),
        ],
        [
            'file'    => 'images/hero/hero-3.png',
            'mobile'  => 'images/hero/hero-3-mobile.png',
            'eyebrow' => 'Rilis Terbaru',
            'title'   => 'Silk Satin',
            'accent'  => 'Untuk Acara Spesial',
            'cta'     => 'Lihat Silk',
            'url'     => route('shop.index', ['q' => 'Silk']),
        ],
    ];
@endphp

<style>
    /* ===== MOBILE: gambar tinggi, fade ke cream, teks naik menimpa fade ===== */
    #hero-campaign { position: relative; display: grid; background: #FFF9F5; overflow: hidden; }
    #hero-campaign .hero-slide { grid-area: 1 / 1; display: flex; flex-direction: column; }

    #hero-campaign .hero-media { position: relative; aspect-ratio: 4 / 5; background: #F3EAEE; overflow: hidden; }
    #hero-campaign .hero-media img {
        position: absolute; inset: 0; width: 100%; height: 100%;
        object-fit: cover; object-position: 80% center;
        transform: scale(1); transition: transform 6s ease-out;
    }
    #hero-campaign .hero-slide.opacity-100 .hero-media img { transform: scale(1.05); }

    #hero-campaign .hero-shade {
        display: block; position: absolute; inset: 0;
        background: linear-gradient(to top, #FFF9F5 0%, rgba(255,249,245,.9) 16%, rgba(255,249,245,0) 46%);
    }

    #hero-campaign .hero-copy {
        position: relative; z-index: 2; margin-top: -6.5rem;
        padding: 0 1.5rem 3.75rem; text-align: center;
    }
    #hero-campaign .hero-eyebrow {
        display: inline-flex; align-items: center; gap: .75rem;
        font-size: 11px; font-weight: 600; letter-spacing: .28em;
        text-transform: uppercase; color: #b97897; margin-bottom: .75rem;
    }
    #hero-campaign .hero-eyebrow::before,
    #hero-campaign .hero-eyebrow::after {
        content: ""; width: 1.5rem; height: 1px; background: currentColor; opacity: .6;
    }
    #hero-campaign .hero-title { font-size: 2.5rem; line-height: 1.05; }
    #hero-campaign .hero-cta {
        display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
        margin-top: 1.5rem; min-width: 14rem; padding: .85rem 1.75rem;
        border-radius: 9999px; background: #d98faf; color: #fff;
        font-size: .875rem; font-weight: 500;
        box-shadow: 0 10px 25px rgba(217,143,175,.35);
        transition: background-color .2s ease, transform .2s ease;
    }
    #hero-campaign .hero-cta:hover { background: #b97897; }
    #hero-campaign .hero-cta:active { transform: scale(.97); }

    #hero-campaign .hero-dots {
        position: absolute; left: 0; right: 0; bottom: 1.25rem;
        display: flex; justify-content: center; gap: .5rem; z-index: 10;
    }

    @media (prefers-reduced-motion: reduce) {
        #hero-campaign .hero-media img { transition: none; transform: none !important; }
    }

    /* ===== DESKTOP: full-bleed, teks di atas gambar ===== */
    @media (min-width: 1024px) {
        #hero-campaign { display: block; aspect-ratio: 16 / 7; max-height: 52rem; }
        #hero-campaign .hero-slide { position: absolute; inset: 0; display: block; }
        #hero-campaign .hero-media { position: absolute; inset: 0; aspect-ratio: auto; }
        #hero-campaign .hero-media img { object-position: 70% center; }
        #hero-campaign .hero-shade {
            background: linear-gradient(to right, rgba(255,249,245,.8), rgba(255,249,245,.25), transparent);
        }
        #hero-campaign .hero-copy {
            position: relative; height: 100%; max-width: 80rem; margin: 0 auto;
            padding: 0 2rem; display: flex; flex-direction: column;
            justify-content: center; align-items: flex-start; text-align: left;
        }
        #hero-campaign .hero-eyebrow::before { display: none; }
        #hero-campaign .hero-title { font-size: 3.75rem; }
        #hero-campaign .hero-cta { min-width: 0; margin-top: 1.75rem; }
        #hero-campaign .hero-dots { left: auto; right: 2rem; justify-content: flex-end; }
    }
</style>

<section id="hero-campaign" aria-roledescription="carousel" aria-label="Kampanye MH Scarf">

    @foreach ($heroSlides as $i => $slide)
        <div class="hero-slide transition-opacity duration-700 ease-out motion-reduce:transition-none
                    {{ $i === 0 ? 'opacity-100' : 'opacity-0 pointer-events-none' }}"
             data-hero-slide
             aria-hidden="{{ $i === 0 ? 'false' : 'true' }}">

            <div class="hero-media">
                @if (file_exists(public_path($slide['file'])))
                    <picture>
                        @if (file_exists(public_path($slide['mobile'])))
                            <source media="(max-width: 1023px)" srcset="{{ asset($slide['mobile']) }}">
                        @endif
                        <img src="{{ asset($slide['file']) }}"
                             alt="{{ $slide['title'] }} {{ $slide['accent'] }}"
                             @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif>
                    </picture>
                @endif
                <div class="hero-shade"></div>
            </div>

            <div class="hero-copy">
                <p class="hero-eyebrow">{{ $slide['eyebrow'] }}</p>
                <h2 class="hero-title font-display font-semibold tracking-tight text-ink">
                    {{ $slide['title'] }}
                    <span class="block font-normal italic text-pink-mauve">{{ $slide['accent'] }}</span>
                </h2>
                <a href="{{ $slide['url'] }}" class="hero-cta">
                    <span>{{ $slide['cta'] }}</span>
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    @endforeach

    <div class="hero-dots">
        @foreach ($heroSlides as $i => $slide)
            <button type="button" data-hero-dot="{{ $i }}"
                    aria-label="Slide {{ $i + 1 }}"
                    class="h-2 rounded-full transition-all duration-300 {{ $i === 0 ? 'w-6 bg-pink-mauve' : 'w-2 bg-ink/30' }}"></button>
        @endforeach
    </div>
</section>

<script>
    (function () {
        const root = document.getElementById('hero-campaign');
        if (!root) return;
        const slides = root.querySelectorAll('[data-hero-slide]');
        const dots = root.querySelectorAll('[data-hero-dot]');
        if (slides.length < 2) return;

        let current = 0, timer = null;
        const INTERVAL = 5000;

        function show(n) {
            current = (n + slides.length) % slides.length;
            slides.forEach((s, i) => {
                const on = i === current;
                s.classList.toggle('opacity-100', on);
                s.classList.toggle('opacity-0', !on);
                s.classList.toggle('pointer-events-none', !on);
                s.setAttribute('aria-hidden', on ? 'false' : 'true');
            });
            dots.forEach((d, i) => {
                const on = i === current;
                d.classList.toggle('w-6', on);
                d.classList.toggle('bg-pink-mauve', on);
                d.classList.toggle('w-2', !on);
                d.classList.toggle('bg-ink/30', !on);
            });
        }

        function start() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            stop();
            timer = setInterval(() => show(current + 1), INTERVAL);
        }
        function stop() { if (timer) { clearInterval(timer); timer = null; } }

        dots.forEach((d) => d.addEventListener('click', () => {
            show(parseInt(d.dataset.heroDot, 10));
            start();
        }));
        root.addEventListener('mouseenter', stop);
        root.addEventListener('mouseleave', start);

        // Swipe di mobile
        let x0 = null;
        root.addEventListener('touchstart', (e) => { x0 = e.touches[0].clientX; stop(); }, { passive: true });
        root.addEventListener('touchend', (e) => {
            if (x0 !== null) {
                const dx = e.changedTouches[0].clientX - x0;
                if (Math.abs(dx) > 50) show(current + (dx < 0 ? 1 : -1));
            }
            x0 = null;
            start();
        });

        start();
    })();
</script>

    {{-- =========================================================================
         4. SHOP BY CATEGORY (DESIGN.md v3 §7.4: Editorial category discovery)
         ========================================================================= --}}
    <section id="koleksi-kategori" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 scroll-mt-24">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-3 gap-y-8 sm:gap-x-5 sm:gap-y-10">
        @foreach ($categories->take(8) as $cat)
            <x-category-card :category="$cat" />
        @endforeach
    </div>
</section>

    {{-- =========================================================================
         5. BEST SELLERS / MOST LOVED (DESIGN.md v3 §7.5: High-converting curation)
         ========================================================================= --}}
    @if ($bestSellers->isNotEmpty())
        <section id="best-sellers" class="bg-surface/50 border-y border-line/60 py-14 sm:py-16 md:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-center text-xl font-normal uppercase tracking-[0.25em] text-ink/80 sm:text-2xl">
    Most Loved
</h2>

                <div class="mt-8 sm:mt-10 grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 lg:gap-6">
                    @foreach ($bestSellers as $prod)
                        <x-product-card :product="$prod" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif


    {{-- Editorial campaign / brand story --}}
<section id="tentang-kami" class="relative overflow-hidden border-y border-line/70 bg-[#fbf7f4] py-16 sm:py-20 lg:py-28">
    <div class="pointer-events-none absolute -right-32 top-1/2 h-80 w-80 -translate-y-1/2 rounded-full bg-pink-soft/20 blur-3xl"></div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-10 lg:grid-cols-[0.92fr_1.08fr] lg:gap-20">
            {{-- Lookbook image --}}
            <div class="relative mx-auto w-full max-w-xl lg:mx-0">
                <div class="relative aspect-[4/5] overflow-hidden rounded-[1.5rem] bg-[#f4e9eb] shadow-card ring-1 ring-line/60">
                    @php
                        $campaignImageUrl = Storage::url('products/mh-scarf-campaign.webp');
                    @endphp

                    <img
        src="{{ $campaignImageUrl }}"
        alt="Koleksi scarf MH Scarf"
        class="h-full w-full object-cover transition duration-700 hover:scale-[1.02]"
    >

                    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-ink/25 to-transparent"></div>
                    <p class="absolute bottom-5 left-6 text-[10px] font-medium uppercase tracking-[0.2em] text-white sm:left-8">The everyday edit</p>
                </div>

                {{-- Small caption card creates a more personal lookbook feel --}}
                <div class="absolute -bottom-5 right-4 w-[min(72%,15rem)] rounded-xl border border-line/80 bg-white/95 p-4 shadow-card backdrop-blur sm:-right-5 sm:p-5">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-pink-mauve">Catatan kecil</p>
                    <p class="mt-2 font-display text-base italic leading-snug text-ink">“Pilih yang membuatmu merasa nyaman menjadi diri sendiri.”</p>
                </div>
            </div>

            {{-- Brand narrative --}}
            <div class="pt-5 text-center lg:pt-0 lg:text-left">
                <div class="mb-6 flex items-center justify-center gap-3 lg:justify-start">
                    <span class="h-px w-8 bg-pink-mauve"></span>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-pink-mauve">Tentang MH Scarf</p>
                </div>

                <h2 class="mx-auto max-w-xl font-display text-3xl font-semibold leading-[1.15] text-ink sm:text-4xl lg:mx-0 lg:text-[2.8rem]">
                    Scarf yang mengikuti harimu, bukan sebaliknya.
                </h2>

                <div class="mx-auto mt-6 max-w-lg space-y-4 text-sm leading-7 text-muted lg:mx-0 sm:text-base">
                    <p>
                        Kami memilih scarf yang enak dipakai, mudah dibentuk, dan tetap terlihat rapi setelah menemani banyak kegiatan.
                    </p>
                    <p>
                        Mulai dari warna netral untuk hari yang sibuk hingga aksen lembut untuk momen spesial—semuanya dikurasi supaya mudah masuk ke lemari pakaimu.
                    </p>
                </div>

                <div class="mt-8 flex flex-col items-center gap-4 sm:flex-row lg:justify-start">
                    <x-primary-button :href="route('shop.index')" class="inline-flex min-h-[46px] items-center">
                        Lihat koleksi pilihan
                        <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </x-primary-button>
                    <span class="text-xs text-muted">Dikurasi untuk pemakaian sehari-hari</span>
                </div>
            </div>
        </div>
    </div>
</section>

    {{-- =========================================================================
         8. MATERIAL EDUCATION (DESIGN.md v3 §7.8: Fabric craftsmanship guide)
         ========================================================================= --}}
  @php
    $material = [
        'img'     => 'images/material/material-banner.jpg',
        'mobile'  => 'images/material/material-banner-mobile.jpg', // opsional
        'label'   => 'Voal Premium',
        'tagline' => 'Rapi dari Pagi',
        'tone'    => 'dark', // 'dark' = teks gelap, 'light' = teks putih
        'url'     => route('shop.index'),
    ];
@endphp

<style>
    .mat-banner { position: relative; display: block; width: 100%; overflow: hidden; background: #F3EAEE; aspect-ratio: 4 / 5; }
    .mat-banner img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .7s ease-out; }
    .mat-banner:hover img { transform: scale(1.03); }
    .mat-copy {
        position: absolute; left: 0; right: 0; bottom: 0; z-index: 2;
        display: flex; flex-direction: column; align-items: center; gap: .9rem;
        padding: 0 1.25rem 2rem;
    }
    .mat-pill {
        display: inline-flex; align-items: center; justify-content: center;
        padding: .75rem 2rem; border: 2px solid currentColor; border-radius: 9999px;
        font-size: 1.05rem; font-weight: 600; transition: background-color .2s ease, color .2s ease;
    }
    .mat-tagline { font-size: 1.5rem; font-weight: 700; line-height: 1.1; }
    .mat-banner[data-tone="dark"]  { color: #3a3033; }
    .mat-banner[data-tone="light"] { color: #ffffff; }
    .mat-banner[data-tone="dark"]:hover .mat-pill  { background: #3a3033; color: #fff; }
    .mat-banner[data-tone="light"]:hover .mat-pill { background: #fff; color: #3a3033; }

    @media (min-width: 1024px) {
        .mat-banner { aspect-ratio: 16 / 7; max-height: 52rem; }
        .mat-copy {
            top: 0; flex-direction: row; align-items: center; justify-content: space-between;
            padding: 0 6%; gap: 2rem;
        }
        .mat-pill { font-size: 1.5rem; padding: 1.1rem 3rem; }
        .mat-tagline { font-size: 2.5rem; }
    }
</style>

<section id="material-education" aria-label="Karakteristik material">
    <a href="{{ $material['url'] }}" class="mat-banner" data-tone="{{ $material['tone'] }}">
        @if (file_exists(public_path($material['img'])))
            <picture>
                @if (file_exists(public_path($material['mobile'])))
                    <source media="(max-width: 1023px)" srcset="{{ asset($material['mobile']) }}">
                @endif
                <img src="{{ asset($material['img']) }}" alt="{{ $material['label'] }}" loading="lazy">
            </picture>
        @endif
        <div class="mat-copy">
            <span class="mat-pill">{{ $material['label'] }}</span>
            <span class="mat-tagline font-display">{{ $material['tagline'] }}</span>
        </div>
    </a>
</section>

    {{-- =========================================================================
         11. JOURNAL / STYLE GUIDE (DESIGN.md v3 §7.11: Visual styling guide placeholder)
         ========================================================================= --}}
    @php
    $diaries = [
        [
            'img'   => 'images/diaries/diary-1.jpg',
            'title' => 'Daily Minimalist Wrap',
            'desc'  => 'Teknik lilit praktis tanpa lipatan rumit, cocok untuk kuliah dan rapat pagi.',
            'url'   => route('shop.index', ['q' => 'Voal']),
        ],
        [
            'img'   => 'images/diaries/diary-2.jpg',
            'title' => 'Pastel & Earthy Harmony',
            'desc'  => 'Palet rose blush, cream, dan sage yang tenang dan menyegarkan.',
            'url'   => route('shop.index'),
        ],
        [
            'img'   => 'images/diaries/diary-3.jpg',
            'title' => 'The Evening Elegance',
            'desc'  => 'Silk satin berpadu gaun malam atau gamis formal dengan drape yang mewah.',
            'url'   => route('shop.index', ['q' => 'Silk']),
        ],
    ];
@endphp

<section id="style-journal" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 md:py-20 scroll-mt-24">
    <h2 class="text-center text-xl font-normal uppercase tracking-[0.2em] text-ink/80 sm:text-2xl">
        MH Scarf Diaries
    </h2>

    <div class="mt-10 sm:mt-14 grid sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-10">
        @foreach ($diaries as $d)
            <a href="{{ $d['url'] }}" class="group block text-center">
                <div class="aspect-[5/4] overflow-hidden bg-[#F6EEF1]">
                    @if (file_exists(public_path($d['img'])))
                        <img src="{{ asset($d['img']) }}" alt="{{ $d['title'] }}" loading="lazy"
                             class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.04]">
                    @else
                        <div class="h-full w-full bg-gradient-to-br from-[#FAF5F7] via-[#F6EEF1] to-[#EFE2E7]"></div>
                    @endif
                </div>
                <h3 class="mt-4 text-sm font-medium uppercase tracking-wide text-ink transition-colors group-hover:text-pink-mauve">
                    {{ $d['title'] }}
                </h3>
                <p class="mx-auto mt-1.5 max-w-xs text-xs leading-relaxed text-muted">
                    {{ $d['desc'] }}
                </p>
            </a>
        @endforeach
    </div>
</section>

    {{-- =========================================================================
         12. NEWSLETTER (DESIGN.md v3 §7.12: Typography-led community invitation)
         ========================================================================= --}}
    <x-newsletter-section id="newsletter" />



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
