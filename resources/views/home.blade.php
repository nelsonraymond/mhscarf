<x-layouts.app>
    @section('title', 'MH Scarf— Modern Hijab Fashion')

    {{-- =========================================================================
         1. ANNOUNCEMENT BAR  ──▶ Rendered via <x-announcement-bar /> in app layout
         2. NAVBAR            ──▶ Rendered via <x-navbar /> in app layout
         ========================================================================= --}}

    {{-- 3. HERO SLIDER: 3 gambar full-bleed (gaya lozy.id) --}}
@php
    $heroSlides = [
        [
            'file'    => 'images/hero/hero-2.webp',
            'mobile'  => 'images/hero/mobile/hero-2.webp',
        ],
        [
            'file'    => 'images/hero/hero-1.webp',
            'mobile'  => 'images/hero/mobile/hero-1.webp',
        ],

    ];
@endphp


   <x-promo-popup image="{{ asset('images/promo-okt.webp') }}"
               :link="route('shop.index')"
               alt="Promo Oktober"
               key="promo-oktober-2026"
               :delay="1000" />

<style>
    /* Hero mobile-first: satu media wrapper, tanpa wrapper/overlay ganda */
    #hero-campaign {
        position: relative;
        display: grid;
        width: 100%;
        overflow: hidden;
        background: #FFF9F5;
    }
    #hero-campaign .hero-slide {
        grid-area: 1 / 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }
    #hero-campaign .hero-media {
        position: relative;
        width: 100%;
        aspect-ratio: 3 / 4;
        overflow: hidden;
        background: #F3EAEE;
    }
    #hero-campaign .hero-media picture,
    #hero-campaign .hero-media img {
        display: block;
        width: 100%;
        height: 100%;
    }
    #hero-campaign .hero-media img {
        position: absolute;
        inset: 0;
        object-fit: cover;
        object-position: 65% 25%;
        transform: scale(1.01);
        transition: transform 6s ease-out;
        image-rendering: auto;
    }
    #hero-campaign .hero-slide.opacity-100 .hero-media img { transform: scale(1.04); }
    #hero-campaign .hero-shade {
        position: absolute;
        inset: 0;
        z-index: 1;
        pointer-events: none;
        background: linear-gradient(to top, #FFF9F5 0%, rgba(255,249,245,.9) 14%, rgba(255,249,245,0) 42%);
    }
    #hero-campaign .hero-copy {
        position: relative;
        z-index: 2;
        margin-top: -4.5rem;
        padding: 0 1.25rem 2.5rem;
        text-align: center;
    }
    #hero-campaign .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
        margin-bottom: .6rem;
        color: #b97897;
        font-size: 10px;
        font-weight: 600;
        letter-spacing: .24em;
        text-transform: uppercase;
    }
    #hero-campaign .hero-eyebrow::before,
    #hero-campaign .hero-eyebrow::after {
        content: "";
        width: 1.25rem;
        height: 1px;
        background: currentColor;
        opacity: .6;
    }
    #hero-campaign .hero-title { font-size: 1.875rem; line-height: 1.1; }
    #hero-campaign .hero-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        min-width: 12rem;
        margin-top: 1.25rem;
        padding: .75rem 1.5rem;
        border-radius: 9999px;
        background: #d98faf;
        color: #fff;
        font-size: .8125rem;
        font-weight: 500;
        box-shadow: 0 10px 25px rgba(217,143,175,.35);
        transition: background-color .2s ease, transform .2s ease;
    }
    #hero-campaign .hero-cta:hover { background: #b97897; }
    #hero-campaign .hero-cta:active { transform: scale(.97); }
    #hero-campaign .hero-dots {
        position: absolute;
        right: 0;
        bottom: .9rem;
        left: 0;
        z-index: 10;
        display: flex;
        justify-content: center;
        gap: .4rem;
    }

    @media (min-width: 480px) {
        #hero-campaign .hero-media { aspect-ratio: 4 / 5; }
        #hero-campaign .hero-title { font-size: 2.25rem; }
    }
    @media (min-width: 640px) and (max-width: 1023px) {
        #hero-campaign .hero-media { aspect-ratio: 16 / 10; }
        #hero-campaign .hero-copy { margin-top: -5.5rem; padding-bottom: 3rem; }
        #hero-campaign .hero-title { font-size: 2.75rem; }
    }
    @media (min-width: 1024px) {
        #hero-campaign {
            display: block;
            height: clamp(26rem, 43.75vw, 52rem);
        }
        #hero-campaign .hero-slide {
            position: absolute;
            inset: 0;
            display: block;
        }
        #hero-campaign .hero-media {
            position: absolute;
            inset: 0;
            height: 100%;
            aspect-ratio: auto;
        }
        #hero-campaign .hero-media img {
            object-position: 70% center;
            transform: none;
        }
        #hero-campaign .hero-slide.opacity-100 .hero-media img { transform: none; }
        #hero-campaign .hero-shade {
            background: linear-gradient(to right, rgba(255,249,245,.8), rgba(255,249,245,.25), transparent);
        }
        #hero-campaign .hero-copy {
            position: relative;
            height: 100%;
            max-width: 80rem;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            text-align: left;
        }
        #hero-campaign .hero-eyebrow::before { display: none; }
        #hero-campaign .hero-title { font-size: 3.75rem; }
        #hero-campaign .hero-cta { min-width: 0; margin-top: 1.75rem; }
        #hero-campaign .hero-dots { right: 2rem; left: auto; justify-content: flex-end; }
    }
    @media (prefers-reduced-motion: reduce) {
        #hero-campaign .hero-media img { transition: none; transform: none !important; }
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
                        {{-- File mobile opsional; jika tidak ada, desktop image tetap dipakai. --}}
                        @if (!empty($slide['mobile']) && file_exists(public_path($slide['mobile'])))
                            <source media="(max-width: 1023px)" srcset="{{ asset($slide['mobile']) }}">
                        @endif
                        <img src="{{ asset($slide['file']) }}"
                             alt=""
                             decoding="async"
                             @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif>
                    </picture>
                @endif
                <div class="hero-shade"></div>
            </div>

            <div class="hero-copy">
                <h2 class="hero-title font-display font-semibold tracking-tight text-ink"></h2>
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
    {{-- =========================================================================
         8. MATERIAL EDUCATION (DESIGN.md v3 §7.8: Fabric craftsmanship guide)
         ========================================================================= --}}
@php
    $material = [
        'img'    => 'images/material/material-banner.webp',
        'mobile' => 'images/material/material-banner-mobile.webp',
        'url'    => route('shop.index'),
    ];
@endphp

<style>
    .mat-banner {
        display: block;
        width: 100%;
        overflow: hidden;
        line-height: 0;
    }

    .mat-banner picture {
        display: block;
        width: 100%;
    }

    .mat-banner img {
        display: block;
        width: 100%;
        height: auto;
        object-fit: contain;
    }

    /* Tampilan desktop */
    @media (min-width: 1024px) {
        .mat-banner img {
            width: 100%;
            height: auto;
        }
    }

    /* Tampilan mobile */
    @media (max-width: 1023px) {
        .mat-banner img {
            width: 100%;
            height: auto;
        }
    }
</style>

<section id="material-education" aria-label="Banner material">
    <a href="{{ $material['url'] }}" class="mat-banner">
        <picture>
            @if (file_exists(public_path($material['mobile'])))
                <source
                    media="(max-width: 1023px)"
                    srcset="{{ asset($material['mobile']) }}"
                >
            @endif

            <img
                src="{{ asset($material['img']) }}"
                alt="Koleksi material hijab MH Scarf"
                loading="lazy"
            >
        </picture>
    </a>
</section>

    {{-- =========================================================================
     11. JOURNAL / STYLE GUIDE
     ========================================================================= --}}
@php
    $diaries = collect(config('journal.articles'))->map(fn ($a) => [
        'img'   => $a['img'],
        'title' => $a['title'],
        'desc'  => $a['desc'],
        'url'   => route('journal.show', $a['slug']),
    ])->all();
@endphp

<section id="style-journal"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 md:py-20 scroll-mt-24">

    <div class="mx-auto max-w-2xl text-center">
        <h2 class="mt-3 text-xl font-normal uppercase tracking-[0.2em] text-ink/80 sm:text-2xl">
            Stories in Every Drape
        </h2>

        <p class="mt-4 text-sm leading-relaxed text-muted">
            Inspirasi gaya, cerita warna, dan cara menemukan keanggunan
            dalam setiap helai scarf MH.
        </p>
    </div>

    <div class="mt-10 grid gap-x-5 gap-y-10 sm:mt-14 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($diaries as $d)
            <a href="{{ $d['url'] }}"
                class="group block text-center"
                aria-label="Read {{ $d['title'] }}">

                <div class="aspect-[5/4] overflow-hidden bg-[#F6EEF1]">
                    @if (file_exists(public_path($d['img'])))
                        <img src="{{ asset($d['img']) }}"
                            alt="{{ $d['title'] }}"
                            loading="lazy"
                            class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.04]">
                    @else
                        <div class="h-full w-full bg-gradient-to-br from-[#FAF5F7] via-[#F6EEF1] to-[#EFE2E7]"></div>
                    @endif
                </div>

                <h3 class="mt-4 text-sm font-medium uppercase tracking-wide text-ink transition-colors group-hover:text-pink-mauve">
                    {{ $d['title'] }}
                </h3>

                <p class="mx-auto mt-2 max-w-xs text-xs leading-relaxed text-muted">
                    {{ $d['desc'] }}
                </p>

                <span class="mt-4 inline-flex items-center gap-2 border-b border-ink/40 pb-1 text-[10px] font-medium uppercase tracking-[0.2em] text-ink transition-all duration-300 group-hover:gap-3 group-hover:border-pink-mauve group-hover:text-pink-mauve">
                    Read More
                    <span aria-hidden="true">→</span>
                </span>
            </a>
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
