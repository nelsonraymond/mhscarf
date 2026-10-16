{{-- Navbar — MH Scarf
     Referensi: hijabchic. Logo kiri → menu rata kiri di sebelah logo → ikon (cari / akun / keranjang) rata kanan.
     Desktop (lg+): Shop | New In | Best Seller | Collections ▾ | Sale | Journal | About
     Mobile/tablet (<lg): hamburger + logo + ikon, menu lengkap di panel khusus.
     Search = ikon yang membuka panel full-width (semua ukuran layar), bukan input yang selalu tampil. --}}

@php
    $navCategories = \App\Models\Category::query()->active()->orderBy('name')->take(8)->get();

    // Kelas dasar semua link menu desktop (uppercase kecil + tracking, sesuai referensi)
    $navLink = 'relative py-1.5 text-[13px] uppercase tracking-[0.08em] transition-colors duration-200 motion-reduce:transition-none
            hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded-sm
            after:absolute after:left-0 after:right-0 after:bottom-0 after:h-px after:bg-current
            after:origin-left after:scale-x-0 after:transition-transform after:duration-200
            motion-reduce:after:transition-none hover:after:scale-x-100';
$navActive = 'text-ink after:scale-x-100';
$navIdle = 'text-muted';

    $isShopRoot = request()->routeIs('shop.index') && ! request()->hasAny(['sort', 'sale']);
    $isNewIn = request()->routeIs('shop.index') && request('sort') === 'newest';
    $isBest = request()->routeIs('shop.index') && request('sort') === 'best';
    $isSale = request()->routeIs('shop.index') && request()->filled('sale');
    $isCollection = request()->routeIs('shop.category');

    $iconBtn = 'relative inline-flex items-center justify-center min-h-[40px] min-w-[40px] rounded-full text-ink
                hover:bg-pink-soft/40 transition-colors duration-200 motion-reduce:transition-none
                focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep';
@endphp

<header id="mh-navbar"
    class="bg-white/95 backdrop-blur border-b border-line/70 sticky top-0 z-40 transition-shadow duration-300">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10 h-16 lg:h-20 flex items-center gap-4">

        {{-- Hamburger (< lg) --}}
        <button type="button" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="mh-mobile-nav"
            class="js-mobile-nav-toggle lg:hidden -ml-2 {{ $iconBtn }}">
            <svg data-icon="open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-width="1.6" d="M4 7h16M4 12h16M4 17h10"/>
            </svg>
            <svg data-icon="close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-width="1.6" d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>

        {{-- Logo: wordmark serif ringan, lowercase (hapus `lowercase` kalau mau kembali ke huruf kapital) --}}
        <a href="{{ route('home') }}" class="inline-flex items-center space-x-2 shrink-0" aria-label="MH Scarf — Beranda">
            <span class="font-display text-xl font-bold tracking-[0.18em] text-ink uppercase">MH Scarf</span>
        </a>

        {{-- Menu desktop: rata kiri, tepat di sebelah logo --}}
        <nav class="hidden lg:flex items-center gap-7 xl:gap-8 ml-6 xl:ml-10" aria-label="Navigasi utama">
            <a href="{{ route('shop.index') }}"
                @if ($isShopRoot) aria-current="page" @endif
                class="{{ $navLink }} {{ $isShopRoot ? $navActive : $navIdle }}">Shop</a>

            <a href="{{ route('shop.index', ['sort' => 'newest']) }}"
                @if ($isNewIn) aria-current="page" @endif
                class="{{ $navLink }} {{ $isNewIn ? $navActive : $navIdle }}">New In</a>

            <a href="{{ route('shop.index', ['sort' => 'best']) }}"
                @if ($isBest) aria-current="page" @endif
                class="{{ $navLink }} {{ $isBest ? $navActive : $navIdle }}">Best Seller</a>

            {{-- Dropdown: Collections (isi dari kategori aktif di DB) --}}
            <div class="relative" data-dropdown>
                <button type="button" aria-expanded="false" aria-haspopup="true"
                    data-dropdown-toggle
                    class="{{ $navLink }} inline-flex items-center gap-1.5 cursor-pointer {{ $isCollection ? $navActive : $navIdle }}">
                    Collections
                    <svg class="w-3 h-3 transition-transform duration-200 motion-reduce:transition-none" data-chevron fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                    </svg>
                </button>
                {{-- pt-3 = jembatan hover supaya menu tidak menutup saat kursor turun --}}
                <div data-dropdown-menu class="hidden absolute left-0 top-full pt-3 z-50">
                    <ul class="min-w-[220px] bg-white border border-line rounded-xl shadow-card py-2">
                        @forelse ($navCategories as $cat)
                            <li>
                                <a href="{{ route('shop.category', $cat->slug) }}"
                                    class="block px-5 py-2.5 text-[13px] tracking-wide text-muted hover:text-ink hover:bg-pink-soft/30 transition-colors focus-visible:outline-none focus-visible:bg-pink-soft/40">
                                    {{ $cat->name }}
                                </a>
                            </li>
                        @empty
                            <li class="px-5 py-2.5 text-[13px] text-muted">Belum ada koleksi</li>
                        @endforelse
                        <li class="mt-1 pt-1 border-t border-line/70">
                            <a href="{{ route('shop.index') }}" class="block px-5 py-2.5 text-[13px] font-medium text-pink-mauve hover:bg-pink-soft/30 transition-colors">Lihat semua</a>
                        </li>
                    </ul>
                </div>
            </div>

            <a href="{{ route('shop.index', ['sale' => 1]) }}"
                @if ($isSale) aria-current="page" @endif
                class="{{ $navLink }} text-red-600 hover:!text-red-700 {{ $isSale ? 'after:scale-x-100' : '' }}">Sale</a>

            <a href="{{ route('home') }}#style-journal" class="{{ $navLink }} {{ $navIdle }}">Journal</a>
        </nav>

        {{-- Ikon kanan: search / (wishlist) / akun / keranjang --}}
        <div class="ml-auto flex items-center gap-0.5 sm:gap-1.5 shrink-0">
            <button type="button" aria-label="Buka pencarian" aria-expanded="false" aria-controls="mh-search-panel"
                class="js-search-toggle {{ $iconBtn }}">
                <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/>
                </svg>
            </button>

            <a href="{{ route('wishlist.index') }}" class="hidden sm:inline-flex {{ $iconBtn }}" aria-label="Wishlist">
                <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.6-9.2-9A5.2 5.2 0 0112 6.5 5.2 5.2 0 0121.2 11C19 15.4 12 20 12 20z"/>
                </svg>
            </a>

            {{-- Akun: guest → langsung ke login; user login → dropdown kecil --}}
            @auth
                <div class="relative hidden sm:block" data-dropdown>
                    <button type="button" aria-label="Akun" aria-expanded="false" aria-haspopup="true" data-dropdown-toggle class="{{ $iconBtn }} cursor-pointer">
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 20c1-4 4.5-6 8-6s7 2 8 6"/>
                        </svg>
                    </button>
                    <div data-dropdown-menu class="hidden absolute right-0 top-full pt-3 z-50">
                        <ul class="min-w-[190px] bg-white border border-line rounded-xl shadow-card py-2 text-[13px]">
                            @if (auth()->user()->isAdmin())
                                <li><a href="{{ route('admin.dashboard') }}" class="block px-5 py-2.5 font-medium text-pink-mauve hover:bg-pink-soft/30 transition-colors">Dashboard admin</a></li>
                            @else
                                <li><a href="{{ route('orders.index') }}" class="block px-5 py-2.5 text-ink hover:bg-pink-soft/30 transition-colors">Pesanan saya</a></li>
                                <li><a href="{{ route('profile') }}" class="block px-5 py-2.5 text-ink hover:bg-pink-soft/30 transition-colors">Akun saya</a></li>
                            @endif
                            <li class="mt-1 pt-1 border-t border-line/70">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-5 py-2.5 text-muted hover:text-pink-mauve hover:bg-pink-soft/30 transition-colors cursor-pointer">Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="hidden sm:inline-flex {{ $iconBtn }}" aria-label="Masuk">
                    <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 20c1-4 4.5-6 8-6s7 2 8 6"/>
                    </svg>
                </a>
            @endauth

            <a href="{{ route('cart.index') }}" class="{{ $iconBtn }}" aria-label="Keranjang">
                <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 8h12l-1 12H7L6 8zM9 8V6a3 3 0 016 0v2"/>
                </svg>
                <x-cart-badge-count />
            </a>
        </div>
    </div>
</header>

{{-- Panel pencarian full-width (semua ukuran layar) --}}
<div id="mh-search-panel" class="hidden sticky top-16 lg:top-20 z-30 bg-white border-b border-line shadow-card">
    <form method="GET" action="{{ route('shop.index') }}" role="search" class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10 py-4 flex items-center gap-3">
        <label for="panel-search" class="sr-only">Cari produk</label>
        <div class="relative flex-1">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/>
            </svg>
            <input id="panel-search" type="search" name="q" value="{{ request('q') }}" placeholder="Cari hijab, bahan, kategori…"
                class="w-full pl-11 pr-4 py-3 rounded-xl border border-line bg-cream text-sm placeholder:text-muted/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
        </div>
        <button type="submit" class="shrink-0 inline-flex items-center justify-center px-5 py-3 rounded-xl bg-pink-deep text-white text-sm font-medium min-h-[44px] cursor-pointer hover:bg-pink-mauve transition-colors">Cari</button>
    </form>
</div>

{{-- Panel navigasi mobile / tablet --}}
<div id="mh-mobile-nav" class="hidden lg:hidden sticky top-16 z-30 bg-white border-b border-line shadow-card">
    <div class="px-4 sm:px-6 py-3 max-h-[calc(100dvh-4rem)] overflow-y-auto overscroll-contain">

        <nav class="grid" aria-label="Navigasi mobile">
            <a href="{{ route('shop.index') }}" class="py-3.5 text-[13px] uppercase tracking-[0.08em] text-ink border-b border-line/60 {{ $isShopRoot ? 'font-semibold' : '' }}">Shop</a>
            <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="py-3.5 text-[13px] uppercase tracking-[0.08em] text-ink border-b border-line/60">New In</a>
            <a href="{{ route('shop.index', ['sort' => 'best']) }}" class="py-3.5 text-[13px] uppercase tracking-[0.08em] text-ink border-b border-line/60">Best Seller</a>

            {{-- Collections: accordion --}}
            <div class="border-b border-line/60" data-accordion>
                <button type="button" aria-expanded="false" data-accordion-toggle
                    class="w-full flex items-center justify-between py-3.5 text-[13px] uppercase tracking-[0.08em] text-ink cursor-pointer">
                    Collections
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 motion-reduce:transition-none" data-chevron fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                    </svg>
                </button>
                <div data-accordion-panel class="hidden pb-3 grid">
                    @foreach ($navCategories as $cat)
                        <a href="{{ route('shop.category', $cat->slug) }}" class="py-2.5 pl-4 text-sm text-muted hover:text-ink">{{ $cat->name }}</a>
                    @endforeach
                    <a href="{{ route('shop.index') }}" class="py-2.5 pl-4 text-sm font-medium text-pink-mauve">Lihat semua</a>
                </div>
            </div>

            <a href="{{ route('shop.index', ['sale' => 1]) }}" class="py-3.5 text-[13px] uppercase tracking-[0.08em] text-red-600 border-b border-line/60">Sale</a>
            <a href="{{ route('home') }}#style-journal" class="py-3.5 text-[13px] uppercase tracking-[0.08em] text-ink border-b border-line/60">Journal</a>
            <a href="{{ route('home') }}#tentang-kami" class="py-3.5 text-[13px] uppercase tracking-[0.08em] text-ink border-b border-line/60">About</a>
        </nav>

        {{-- Akun & wishlist (ikon ini disembunyikan di layar kecil, jadi pindah ke sini) --}}
        <div class="grid pt-1 pb-2">
            <a href="{{ route('wishlist.index') }}" class="py-3 text-sm text-ink border-b border-line/60">Wishlist</a>
            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="py-3 text-sm font-medium text-pink-mauve border-b border-line/60">Dashboard admin</a>
                @else
                    <a href="{{ route('orders.index') }}" class="py-3 text-sm text-ink border-b border-line/60">Pesanan saya</a>
                    <a href="{{ route('profile') }}" class="py-3 text-sm text-ink border-b border-line/60">Akun saya</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="py-3 text-sm text-muted text-left cursor-pointer">Keluar</button>
                </form>
            @else
                <div class="grid grid-cols-2 gap-3 pt-4">
                    <a href="{{ route('login') }}" class="inline-flex justify-center items-center rounded-xl border border-pink-deep text-pink-deep text-sm font-medium min-h-[44px]">Masuk</a>
                    <a href="{{ route('register') }}" class="inline-flex justify-center items-center rounded-xl bg-pink-deep text-white text-sm font-medium min-h-[44px]">Daftar</a>
                </div>
            @endauth
        </div>
    </div>
</div>

<script>
    // Navbar interactions — bayangan saat scroll, menu mobile, panel search, dropdown desktop, accordion mobile.
    (function () {
        const nav = document.getElementById('mh-navbar');
        const mobileNav = document.getElementById('mh-mobile-nav');
        const searchPanel = document.getElementById('mh-search-panel');
        const navToggle = nav?.querySelector('.js-mobile-nav-toggle');
        const searchToggle = nav?.querySelector('.js-search-toggle');
        const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

        // Hairline shadow setelah halaman di-scroll
        if (nav) {
            const setShadow = () => nav.classList.toggle('shadow-card', window.scrollY > 8);
            window.addEventListener('scroll', setShadow, { passive: true });
            setShadow();
        }

        /* ---------- Dropdown desktop (hover di mouse, klik/keyboard di touch) ---------- */
        const dropdowns = Array.from(document.querySelectorAll('[data-dropdown]'));

        function setDropdown(dd, open) {
            const btn = dd.querySelector('[data-dropdown-toggle]');
            const menu = dd.querySelector('[data-dropdown-menu]');
            menu.classList.toggle('hidden', !open);
            btn.setAttribute('aria-expanded', String(open));
            btn.querySelector('[data-chevron]')?.classList.toggle('rotate-180', open);
        }
        function closeAllDropdowns(except) {
            dropdowns.forEach((dd) => { if (dd !== except) setDropdown(dd, false); });
        }

        dropdowns.forEach((dd) => {
            const btn = dd.querySelector('[data-dropdown-toggle]');
            btn.addEventListener('click', () => {
                const willOpen = btn.getAttribute('aria-expanded') !== 'true';
                closeAllDropdowns(dd);
                setDropdown(dd, willOpen);
            });
            if (canHover) {
                dd.addEventListener('mouseenter', () => { closeAllDropdowns(dd); setDropdown(dd, true); });
                dd.addEventListener('mouseleave', () => setDropdown(dd, false));
            }
            // Tutup saat fokus keyboard keluar dari dropdown
            dd.addEventListener('focusout', (e) => {
                if (!dd.contains(e.relatedTarget)) setDropdown(dd, false);
            });
        });

        document.addEventListener('click', (e) => {
            dropdowns.forEach((dd) => { if (!dd.contains(e.target)) setDropdown(dd, false); });
        });

        /* ---------- Accordion (mobile) ---------- */
        document.querySelectorAll('[data-accordion]').forEach((acc) => {
            const btn = acc.querySelector('[data-accordion-toggle]');
            const panel = acc.querySelector('[data-accordion-panel]');
            btn.addEventListener('click', () => {
                const open = panel.classList.toggle('hidden') === false;
                btn.setAttribute('aria-expanded', String(open));
                btn.querySelector('[data-chevron]')?.classList.toggle('rotate-180', open);
            });
        });

        /* ---------- Search panel & mobile nav ---------- */
        function closeSearch() {
            if (!searchPanel || searchPanel.classList.contains('hidden')) return;
            searchPanel.classList.add('hidden');
            searchToggle?.setAttribute('aria-expanded', 'false');
            searchToggle?.setAttribute('aria-label', 'Buka pencarian');
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

        navToggle?.addEventListener('click', function () {
            if (!mobileNav) return;
            const isHidden = mobileNav.classList.toggle('hidden');
            this.setAttribute('aria-expanded', String(!isHidden));
            this.setAttribute('aria-label', isHidden ? 'Buka menu navigasi' : 'Tutup menu navigasi');
            this.querySelector('[data-icon="open"]')?.classList.toggle('hidden', !isHidden);
            this.querySelector('[data-icon="close"]')?.classList.toggle('hidden', isHidden);
            closeSearch();
        });

        searchToggle?.addEventListener('click', function () {
            if (!searchPanel) return;
            const isHidden = searchPanel.classList.toggle('hidden');
            this.setAttribute('aria-expanded', String(!isHidden));
            this.setAttribute('aria-label', isHidden ? 'Buka pencarian' : 'Tutup pencarian');
            if (!isHidden) {
                closeMobileNav();
                closeAllDropdowns();
                searchPanel.querySelector('input')?.focus();
            }
        });

        // Tutup menu mobile setelah tap link (termasuk link anchor di halaman yang sama)
        mobileNav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMobileNav));

        // Kalau layar diperbesar ke desktop, pastikan panel mobile tertutup
        window.matchMedia('(min-width: 1024px)').addEventListener('change', (e) => { if (e.matches) closeMobileNav(); });

        // Escape menutup semua overlay
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeMobileNav();
                closeSearch();
                closeAllDropdowns();
            }
        });
    })();
</script>