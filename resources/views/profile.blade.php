<x-layouts.app>
    @section('title', 'Profil Saya — MH Scarf Store')

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 md:pt-12 pb-[calc(2.5rem+env(safe-area-inset-bottom))]">

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="text-xs text-muted mb-4 sm:mb-6">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li>
                    <a href="{{ route('home') }}"
                        class="inline-flex items-center min-h-[44px] sm:min-h-0 hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded transition">Beranda</a>
                </li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-ink font-medium">Profil Saya</li>
            </ol>
        </nav>

        {{-- Profile header card --}}
        <section class="bg-white rounded-3xl border border-line shadow-card overflow-hidden" aria-label="Ringkasan profil">
            {{-- Banner --}}
            <div class="h-24 sm:h-32 bg-gradient-to-br from-pink-soft via-pink-soft/60 to-cream relative overflow-hidden" aria-hidden="true">
                <div class="absolute -top-8 -right-8 w-36 h-36 sm:w-40 sm:h-40 rounded-full bg-white/30"></div>
                <div class="absolute -bottom-6 left-2/3 sm:left-1/3 w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-pink-deep/10"></div>
            </div>

            <div class="px-4 sm:px-8 pb-5 sm:pb-7">
                {{-- Mobile: centered & stacked. Desktop: row --}}
                <div class="relative z-10 flex flex-col items-center text-center gap-3 -mt-12 sm:-mt-12 sm:flex-row sm:items-end sm:text-left sm:gap-4">

                    {{-- Avatar (z-10 so banner shapes never overlap it) --}}
                    <div class="relative z-10 w-24 h-24 rounded-full bg-gradient-to-br from-pink-deep to-pink-mauve text-white flex items-center justify-center font-display text-4xl font-semibold ring-4 ring-white shadow-card shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>

                    {{-- Name + role + email --}}
                    <div class="flex-1 min-w-0 w-full sm:pb-1">
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-2.5 gap-y-1.5">
                            <h1 class="font-display text-xl sm:text-3xl font-bold text-ink leading-tight [overflow-wrap:anywhere]">{{ $user->name }}</h1>
                        </div>
                        <p class="text-sm text-muted mt-1 [overflow-wrap:anywhere]">{{ $user->email }}</p>
                    </div>

                    {{-- Edit button: full width on mobile --}}
                    <a href="{{ route('profile.edit') }}"
                        class="touch-manipulation inline-flex w-full sm:w-auto items-center justify-center gap-2 px-5 h-12 sm:h-11 rounded-xl bg-pink-deep text-white text-sm font-medium transition active:scale-[0.98] active:bg-pink-mauve [@media(hover:hover)]:hover:bg-pink-mauve focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-pink-deep shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                        Edit Profil
                    </a>
                </div>

                {{-- Personal data (email dihapus karena sudah tampil di header) --}}
                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-5 sm:mt-7">
                    <div class="col-span-2 sm:col-span-1 p-4 rounded-2xl bg-cream/70 border border-line/70">
                        <dt class="text-[10px] uppercase tracking-wider text-muted font-semibold">Nomor Telepon / WhatsApp</dt>
                        <dd class="text-sm font-medium text-ink mt-1">{{ $user->phone ?: 'Belum ditambahkan' }}</dd>
                    </div>

                    <div class="p-4 rounded-2xl bg-cream/70 border border-line/70">
                        <dt class="text-[10px] uppercase tracking-wider text-muted font-semibold">Status Email</dt>
                        <dd class="text-sm font-medium mt-1">
                            @if ($user->email_verified_at)
                                <span class="inline-flex items-center gap-1.5 text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                                    Terverifikasi
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-muted">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400" aria-hidden="true"></span>
                                    Belum verifikasi
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div class="p-4 rounded-2xl bg-cream/70 border border-line/70">
                        <dt class="text-[10px] uppercase tracking-wider text-muted font-semibold">Bergabung Sejak</dt>
                        <dd class="text-sm font-medium text-ink mt-1">{{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : '-' }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        {{-- Shortcuts --}}
        <h2 class="font-display text-lg font-bold text-ink mt-8 sm:mt-10 mb-3 sm:mb-4">Akses Cepat</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">

            {{-- Pesanan --}}
            <a href="{{ route('orders.index') }}"
                class="group touch-manipulation relative flex items-center gap-4 p-4 sm:p-5 sm:flex-col sm:items-stretch sm:gap-0 rounded-2xl border border-line bg-white shadow-card transition duration-300 active:scale-[0.98] active:border-pink-deep [@media(hover:hover)]:hover:-translate-y-1 [@media(hover:hover)]:hover:border-pink-deep [@media(hover:hover)]:hover:shadow-card-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
                <div class="w-11 h-11 shrink-0 sm:self-start rounded-xl bg-pink-soft/40 text-pink-deep flex items-center justify-center transition-colors group-active:bg-pink-deep group-active:text-white [@media(hover:hover)]:group-hover:bg-pink-deep [@media(hover:hover)]:group-hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div class="flex-1 min-w-0 sm:mt-4">
                    <span class="block text-sm font-semibold text-ink">Pesanan Saya</span>
                    <span class="text-xs text-muted mt-0.5 block">Riwayat transaksi &amp; status paket</span>
                    <span class="hidden sm:inline-flex text-xs text-pink-deep font-medium mt-4 items-center gap-1">
                        Lihat Pesanan <span aria-hidden="true" class="transition-transform group-hover:translate-x-1">→</span>
                    </span>
                </div>
                <span class="font-display text-2xl sm:text-3xl font-bold text-ink/80 leading-none sm:absolute sm:top-5 sm:right-5">{{ $ordersCount }}</span>
                <svg class="w-4 h-4 text-muted shrink-0 sm:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>

            {{-- Alamat --}}
            <a href="{{ route('addresses.index') }}"
                class="group touch-manipulation relative flex items-center gap-4 p-4 sm:p-5 sm:flex-col sm:items-stretch sm:gap-0 rounded-2xl border border-line bg-white shadow-card transition duration-300 active:scale-[0.98] active:border-pink-deep [@media(hover:hover)]:hover:-translate-y-1 [@media(hover:hover)]:hover:border-pink-deep [@media(hover:hover)]:hover:shadow-card-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
                <div class="w-11 h-11 shrink-0 sm:self-start rounded-xl bg-pink-soft/40 text-pink-deep flex items-center justify-center transition-colors group-active:bg-pink-deep group-active:text-white [@media(hover:hover)]:group-hover:bg-pink-deep [@media(hover:hover)]:group-hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0 sm:mt-4">
                    <span class="block text-sm font-semibold text-ink">Buku Alamat</span>
                    <span class="text-xs text-muted mt-0.5 block">Kelola alamat pengiriman pesanan</span>
                    <span class="hidden sm:inline-flex text-xs text-pink-deep font-medium mt-4 items-center gap-1">
                        Kelola Alamat <span aria-hidden="true" class="transition-transform group-hover:translate-x-1">→</span>
                    </span>
                </div>
                <span class="font-display text-2xl sm:text-3xl font-bold text-ink/80 leading-none sm:absolute sm:top-5 sm:right-5">{{ $addressesCount }}</span>
                <svg class="w-4 h-4 text-muted shrink-0 sm:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>

            {{-- Wishlist --}}
            <a href="{{ route('wishlist.index') }}"
                class="group touch-manipulation relative flex items-center gap-4 p-4 sm:p-5 sm:flex-col sm:items-stretch sm:gap-0 rounded-2xl border border-line bg-white shadow-card transition duration-300 active:scale-[0.98] active:border-pink-deep [@media(hover:hover)]:hover:-translate-y-1 [@media(hover:hover)]:hover:border-pink-deep [@media(hover:hover)]:hover:shadow-card-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
                <div class="w-11 h-11 shrink-0 sm:self-start rounded-xl bg-pink-soft/40 text-pink-deep flex items-center justify-center transition-colors group-active:bg-pink-deep group-active:text-white [@media(hover:hover)]:group-hover:bg-pink-deep [@media(hover:hover)]:group-hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.6-9.2-9A5.2 5.2 0 0112 6.5 5.2 5.2 0 0121.2 11C19 15.4 12 20 12 20z"/></svg>
                </div>
                <div class="flex-1 min-w-0 sm:mt-4">
                    <span class="block text-sm font-semibold text-ink">Wishlist Saya</span>
                    <span class="text-xs text-muted mt-0.5 block">Koleksi hijab yang Anda favoritkan</span>
                    <span class="hidden sm:inline-flex text-xs text-pink-deep font-medium mt-4 items-center gap-1">
                        Buka Wishlist <span aria-hidden="true" class="transition-transform group-hover:translate-x-1">→</span>
                    </span>
                </div>
                <span class="font-display text-2xl sm:text-3xl font-bold text-ink/80 leading-none sm:absolute sm:top-5 sm:right-5">{{ $wishlistCount }}</span>
                <svg class="w-4 h-4 text-muted shrink-0 sm:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        {{-- Logout (secondary action) --}}
        <div class="mt-8 sm:mt-10 pt-6 border-t border-line">
            <form method="POST" action="{{ route('logout') }}" class="flex sm:justify-end">
                @csrf
                <button type="submit"
                    class="touch-manipulation inline-flex w-full sm:w-auto items-center justify-center gap-2 px-5 h-12 sm:h-11 rounded-xl border border-line bg-white text-sm font-medium text-muted transition cursor-pointer active:scale-[0.98] active:border-rose-300 active:bg-rose-50 active:text-rose-600 [@media(hover:hover)]:hover:border-rose-300 [@media(hover:hover)]:hover:bg-rose-50 [@media(hover:hover)]:hover:text-rose-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                    Keluar dari Akun
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>