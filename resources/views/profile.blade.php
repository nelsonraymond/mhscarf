<x-layouts.app>
    @section('title', 'Profil Saya — MH Scarf Store')

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="text-xs text-muted mb-6">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li>
                    <a href="{{ route('home') }}" class="hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded transition">Beranda</a>
                </li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-ink font-medium">Profil Saya</li>
            </ol>
        </nav>

        {{-- Header Section --}}
        <div class="mb-8">
            <p class="text-[11px] uppercase tracking-[0.2em] text-pink-mauve font-semibold mb-1">Pengaturan Akun</p>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink">Profil Pelanggan</h1>
            <p class="text-sm text-muted mt-1.5">Informasi akun terdaftar dan pintasan navigasi belanja Anda di MH Scarf Store</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Left column: Profile card summary --}}
            <div class="md:col-span-1">
                <div class="bg-white rounded-2xl border border-line p-6 shadow-card text-center">
                    <div class="w-18 h-18 rounded-full bg-pink-soft/50 text-pink-mauve flex items-center justify-center mx-auto mb-4 font-display text-2xl font-semibold border border-pink-mauve/20">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h2 class="font-display text-lg font-bold text-ink leading-snug">{{ $user->name }}</h2>
                    <p class="text-xs text-muted mt-0.5 truncate">{{ $user->email }}</p>
                    <div class="mt-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-pink-soft/60 text-pink-mauve border border-pink-mauve/25">
                            {{ $user->role }}
                        </span>
                    </div>

                    <div class="mt-6 pt-5 border-t border-line/60">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-line hover:border-pink-deep bg-cream hover:bg-pink-soft/30 text-xs font-medium text-ink transition cursor-pointer">
                                Keluar dari Akun
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Right column: Account Details & Navigation shortcuts --}}
            <div class="md:col-span-2 space-y-6">

                {{-- Account Details Card --}}
                <div class="bg-white rounded-2xl border border-line p-6 sm:p-7 shadow-card">
                    <h3 class="font-display text-base font-bold text-ink mb-4 pb-3 border-b border-line/60 flex items-center justify-between">
                        <span>Data Pribadi</span>
                        <a href="{{ route('profile.edit') }}" class="text-sm text-pink-deep hover:underline ml-2">Edit Profil</a>
                        <span>Data Pribadi</span>
                        <span class="text-xs text-muted font-normal">Terverifikasi</span>
                    </h3>

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-3.5 rounded-xl bg-cream/70 border border-line/70">
                            <dt class="text-[10px] uppercase tracking-wider text-muted font-semibold">Nama Lengkap</dt>
                            <dd class="text-sm font-medium text-ink mt-1 truncate">{{ $user->name }}</dd>
                        </div>

                        <div class="p-3.5 rounded-xl bg-cream/70 border border-line/70">
                            <dt class="text-[10px] uppercase tracking-wider text-muted font-semibold">Alamat Email</dt>
                            <dd class="text-sm font-medium text-ink mt-1 truncate">{{ $user->email }}</dd>
                        </div>

                        <div class="p-3.5 rounded-xl bg-cream/70 border border-line/70">
                            <dt class="text-[10px] uppercase tracking-wider text-muted font-semibold">Nomor Telepon / WhatsApp</dt>
                            <dd class="text-sm font-medium text-ink mt-1">{{ $user->phone ?? 'Belum ditambahkan' }}</dd>
                        </div>

                        <div class="p-3.5 rounded-xl bg-cream/70 border border-line/70">
                            <dt class="text-[10px] uppercase tracking-wider text-muted font-semibold">Bergabung Sejak</dt>
                            <dd class="text-sm font-medium text-ink mt-1">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Action Shortcuts (All link to existing routes: addresses, orders, wishlist) --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <a href="{{ route('orders.index') }}"
                        class="p-4 rounded-xl border border-line bg-white hover:border-pink-deep hover:bg-pink-soft/20 transition shadow-card flex flex-col justify-between group">
                        <div>
                            <div class="w-8 h-8 rounded-lg bg-pink-soft/40 text-pink-deep flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <span class="block text-sm font-semibold text-ink group-hover:text-pink-deep transition-colors">Pesanan Saya</span>
                            <span class="text-xs text-muted mt-0.5 block">Riwayat transaksi & status paket</span>
                        </div>
                        <span class="text-xs text-pink-deep font-medium mt-3 inline-flex items-center gap-1">
                            Lihat Pesanan <span aria-hidden="true">→</span>
                        </span>
                    </a>

                    <a href="{{ route('addresses.index') }}"
                        class="p-4 rounded-xl border border-line bg-white hover:border-pink-deep hover:bg-pink-soft/20 transition shadow-card flex flex-col justify-between group">
                        <div>
                            <div class="w-8 h-8 rounded-lg bg-pink-soft/40 text-pink-deep flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <span class="block text-sm font-semibold text-ink group-hover:text-pink-deep transition-colors">Buku Alamat</span>
                            <span class="text-xs text-muted mt-0.5 block">Kelola alamat pengiriman pesanan</span>
                        </div>
                        <span class="text-xs text-pink-deep font-medium mt-3 inline-flex items-center gap-1">
                            Kelola Alamat <span aria-hidden="true">→</span>
                        </span>
                    </a>

                    <a href="{{ route('wishlist.index') }}"
                        class="p-4 rounded-xl border border-line bg-white hover:border-pink-deep hover:bg-pink-soft/20 transition shadow-card flex flex-col justify-between group">
                        <div>
                            <div class="w-8 h-8 rounded-lg bg-pink-soft/40 text-pink-deep flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.6-9.2-9A5.2 5.2 0 0112 6.5 5.2 5.2 0 0121.2 11C19 15.4 12 20 12 20z"/>
                                </svg>
                            </div>
                            <span class="block text-sm font-semibold text-ink group-hover:text-pink-deep transition-colors">Wishlist Saya</span>
                            <span class="text-xs text-muted mt-0.5 block">Koleksi hijab yang Anda favoritkan</span>
                        </div>
                        <span class="text-xs text-pink-deep font-medium mt-3 inline-flex items-center gap-1">
                            Buka Wishlist <span aria-hidden="true">→</span>
                        </span>
                    </a>
                </div>

            </div>
        </div>

    </div>
</x-layouts.app>
