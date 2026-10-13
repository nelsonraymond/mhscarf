{{-- Navbar — DESIGN.md §13: sticky, white/cream bg, thin bottom border, restrained shadow.
     Desktop: Logo | Shop | Collections | About | Journal | Search | Wishlist | Cart | Account
     About / Journal anchor to real homepage sections (#tentang-kami, #style-journal) — no dead links. --}}
<header id="MH Scarf-navbar"
    class="bg-white/95 backdrop-blur border-b border-line sticky top-0 z-40 transition-shadow duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">

        {{-- Mobile hamburger (opens dedicated MobileNav, not a collapsed desktop menu) --}}
        <button type="button" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="MH Scarf-mobile-nav"
            class="js-mobile-nav-toggle md:hidden -ml-1 p-2 rounded-lg text-ink hover:bg-pink-soft/40 transition shrink-0 min-h-[40px] min-w-[40px]">
            <svg data-icon="open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h10"/>
            </svg>
            <svg data-icon="close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-width="1.8" d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>

        {{-- Logo: typographic serif wordmark only (DESIGN.md v3 — no floral ornaments) --}}
        <a href="{{ route('home') }}" class="inline-flex items-center space-x-2 shrink-0" aria-label="MH Scarf — Beranda">
            <span class="font-display text-xl font-bold tracking-[0.18em] text-ink uppercase">MH Scarf</span>
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden md:flex items-center space-x-7" aria-label="Navigasi utama">
            <x-nav-link :href="route('shop.index')" :active="request()->routeIs('shop.index')">Shop</x-nav-link>
            <x-nav-link :href="route('shop.category', 'pashmina')" :active="request()->routeIs('shop.category')">Collections</x-nav-link>
            <x-nav-link :href="route('home').'#tentang-kami'" :active="false">About</x-nav-link>
            <x-nav-link :href="route('home').'#style-journal'" :active="false">Journal</x-nav-link>
        </nav>

        {{-- Right actions: search / wishlist / cart / account --}}
        <div class="flex items-center gap-2 sm:gap-4 shrink-0">
            {{-- Search (submits GET /shop?q= — reuses existing catalog search) --}}
            <form method="GET" action="{{ route('shop.index') }}" class="hidden lg:flex items-center relative">
                <label for="desktop-search" class="sr-only">Cari produk</label>
                <input id="desktop-search" type="search" name="q" value="{{ request('q') }}"
                    placeholder="Cari hijab…"
                    class="w-40 xl:w-48 pl-8 pr-3 py-2 rounded-xl border border-line bg-cream text-xs placeholder:text-muted/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep transition">
                <svg class="absolute left-2.5 w-4 h-4 text-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/>
                </svg>
            </form>

            {{-- Mobile search icon — expands a full-width panel per DESIGN.md §13 --}}
            <button type="button" aria-label="Buka pencarian" aria-expanded="false" aria-controls="MH Scarf-search-panel"
                class="js-search-toggle lg:hidden p-2 rounded-lg text-muted hover:text-pink-deep hover:bg-pink-soft/30 transition min-h-[40px] min-w-[40px] inline-flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/>
                </svg>
            </button>

            <a href="{{ route('wishlist.index') }}" class="p-2 rounded-lg text-muted hover:text-pink-deep hover:bg-pink-soft/30 transition min-h-[40px] min-w-[40px] inline-flex items-center justify-center" aria-label="Wishlist">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.6-9.2-9A5.2 5.2 0 0112 6.5 5.2 5.2 0 0121.2 11C19 15.4 12 20 12 20z"/>
                </svg>
            </a>

            <a href="{{ route('cart.index') }}" class="relative p-2 rounded-lg text-muted hover:text-pink-deep hover:bg-pink-soft/30 transition min-h-[40px] min-w-[40px] inline-flex items-center justify-center" aria-label="Keranjang">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 8h12l-1 12H7L6 8zM9 8V6a3 3 0 016 0v2"/>
                </svg>
                <x-cart-badge-count />
            </a>

            @auth
                <div class="hidden sm:flex items-center gap-3 text-xs font-medium">
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-xl bg-pink-soft/50 text-pink-mauve hover:bg-pink-soft transition">Admin</a>
                    @else
                        <a href="{{ route('profile') }}" class="text-muted hover:text-ink transition">Akun</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-muted hover:text-pink-mauve transition cursor-pointer">Keluar</button>
                    </form>
                </div>
                {{-- Compact account icon on small screens handled inside MobileNav panel --}}
            @else
                <div class="hidden sm:flex items-center gap-3 text-xs font-medium shrink-0">
                    <a href="{{ route('login') }}" class="text-muted hover:text-pink-deep transition">Masuk</a>
                    <a href="{{ route('register') }}" class="px-3.5 py-1.5 rounded-xl bg-pink-deep hover:bg-pink-mauve text-white transition shadow-sm">Daftar</a>
                </div>
            @endauth
        </div>
    </div>
</header>

<script>
    // Navbar interactions — scroll shadow, mobile menu, expandable search panel.
    (function () {
        const nav = document.getElementById('MH Scarf-navbar');
        const mobileNav = document.getElementById('MH Scarf-mobile-nav');
        const searchPanel = document.getElementById('MH Scarf-search-panel');
        const navToggle = nav ? nav.querySelector('.js-mobile-nav-toggle') : null;
        const searchToggle = nav ? nav.querySelector('.js-search-toggle') : null;

        // Subtle scroll state: add hairline shadow once the page scrolls (DESIGN.md §30: understated motion)
        if (nav) {
            const setShadow = () => nav.classList.toggle('shadow-card', window.scrollY > 8);
            window.addEventListener('scroll', setShadow, { passive: true });
            setShadow();
        }

        function closeSearch() {
            if (!searchPanel || searchPanel.classList.contains('hidden')) return;
            searchPanel.classList.add('hidden');
            if (searchToggle) {
                searchToggle.setAttribute('aria-expanded', 'false');
                searchToggle.setAttribute('aria-label', 'Buka pencarian');
            }
        }

        function closeMobileNav() {
            if (!mobileNav || mobileNav.classList.contains('hidden')) return;
            mobileNav.classList.add('hidden');
            if (navToggle) {
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.setAttribute('aria-label', 'Buka menu navigasi');
                navToggle.querySelector('[data-icon="open"]')?.classList.remove('hidden');
                navToggle.querySelector('[data-icon="close"]')?.classList.add('hidden');
            }
        }

        if (navToggle && mobileNav) {
            navToggle.addEventListener('click', function () {
                const isHidden = mobileNav.classList.toggle('hidden');
                this.setAttribute('aria-expanded', String(!isHidden));
                this.setAttribute('aria-label', isHidden ? 'Buka menu navigasi' : 'Tutup menu navigasi');
                this.querySelector('[data-icon="open"]')?.classList.toggle('hidden', !isHidden);
                this.querySelector('[data-icon="close"]')?.classList.toggle('hidden', isHidden);
                closeSearch();
            });
        }

        if (searchToggle && searchPanel) {
            searchToggle.addEventListener('click', function () {
                const isHidden = searchPanel.classList.toggle('hidden');
                this.setAttribute('aria-expanded', String(!isHidden));
                this.setAttribute('aria-label', isHidden ? 'Buka pencarian' : 'Tutup pencarian');
                if (!isHidden) {
                    closeMobileNav();
                    searchPanel.querySelector('input')?.focus();
                }
            });
        }

        // Close the mobile menu after tapping a link (avoids sticky panels stacking on navigation)
        if (mobileNav) {
            mobileNav.querySelectorAll('a[href^="#"]').forEach((link) => {
                link.addEventListener('click', closeMobileNav);
            });
        }

        // Escape key closes both overlays
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeMobileNav();
                closeSearch();
            }
        });
    })();
</script>

{{-- Expandable full-width search panel (mobile / tablet) --}}
<div id="MH Scarf-search-panel" class="hidden lg:hidden sticky top-16 z-30 bg-white border-b border-line shadow-card">
    <form method="GET" action="{{ route('shop.index') }}" class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-3">
        <label for="panel-search" class="sr-only">Cari produk</label>
        <div class="relative flex-1">
            <input id="panel-search" type="search" name="q" placeholder="Cari hijab, bahan, kategori…"
                class="w-full pl-10 pr-3 py-3 rounded-xl border border-line bg-cream text-sm placeholder:text-muted/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/>
            </svg>
        </div>
        <button type="submit" class="shrink-0 inline-flex items-center justify-center px-4 py-3 rounded-xl bg-pink-deep text-white text-sm font-medium min-h-[44px] cursor-pointer hover:bg-pink-mauve transition">Cari</button>
    </form>
</div>

{{-- Dedicated mobile navigation panel (intentionally designed, not a collapsed desktop menu) --}}
<div id="MH Scarf-mobile-nav" class="hidden md:hidden sticky top-16 z-30 bg-white border-b border-line shadow-card">
    <div class="px-4 py-4 space-y-5 max-h-[calc(100dvh-8rem)] overflow-y-auto overscroll-contain">
        {{-- Search --}}
        <form method="GET" action="{{ route('shop.index') }}" class="relative">
            <label for="mobile-search" class="sr-only">Cari produk</label>
            <input id="mobile-search" type="search" name="q" placeholder="Cari hijab, bahan, kategori…"
                class="w-full pl-10 pr-3 py-3 rounded-xl border border-line bg-cream text-sm placeholder:text-muted/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/>
            </svg>
        </form>

        {{-- Primary nav --}}
        <nav class="grid" aria-label="Navigasi mobile">
            <a href="{{ route('shop.index') }}" class="flex items-center justify-between py-3 text-sm font-medium text-ink border-b border-line/60 active:bg-pink-soft/20">Shop <span class="text-pink-deep" aria-hidden="true">→</span></a>
            <a href="{{ route('shop.category', 'pashmina') }}" class="flex items-center justify-between py-3 text-sm text-muted border-b border-line/60 active:bg-pink-soft/20">Collections <span class="text-pink-deep" aria-hidden="true">→</span></a>
            <a href="{{ route('home') }}#tentang-kami" class="flex items-center justify-between py-3 text-sm text-muted border-b border-line/60">About <span class="text-pink-deep" aria-hidden="true">→</span></a>
            <a href="{{ route('home') }}#style-journal" class="flex items-center justify-between py-3 text-sm text-muted">Journal <span class="text-pink-deep" aria-hidden="true">→</span></a>
        </nav>

        {{-- Category shortcuts (real DB slugs) --}}
        <div>
            <p class="text-[10px] uppercase tracking-[0.2em] text-pink-mauve font-semibold mb-2">Belanja per gaya</p>
            <div class="flex flex-wrap gap-2">
                @foreach (\App\Models\Category::query()->active()->orderBy('name')->take(6)->get() as $mCat)
                    <a href="{{ route('shop.category', $mCat->slug) }}"
                        class="px-3.5 py-2 rounded-full border border-line bg-cream text-xs text-ink hover:border-pink-deep hover:text-pink-deep transition min-h-[36px] inline-flex items-center">
                        {{ $mCat->name }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Account / help --}}
        <div class="pt-1 border-t border-line grid">
            @auth
                <a href="{{ route('orders.index') }}" class="py-3 text-sm text-ink border-b border-line/60">Pesanan Saya</a>
                <a href="{{ route('profile') }}" class="py-3 text-sm text-ink border-b border-line/60">Akun Saya</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="py-3 text-sm text-muted text-left cursor-pointer">Keluar</button>
                </form>
            @else
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <a href="{{ route('login') }}" class="inline-flex justify-center py-3 rounded-xl border border-pink-deep text-pink-deep text-sm font-medium min-h-[44px]">Masuk</a>
                    <a href="{{ route('register') }}" class="inline-flex justify-center py-3 rounded-xl bg-pink-deep text-white text-sm font-medium min-h-[44px]">Daftar</a>
                </div>
            @endauth
        </div>
    </div>
</div>
