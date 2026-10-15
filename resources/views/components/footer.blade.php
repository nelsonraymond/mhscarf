{{-- Footer — clean layout: brand + 2 link columns, social + copyright on one bottom row. --}}
<footer class="mt-16 bg-white border-t border-line">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6">
        <div class="grid grid-cols-2 md:grid-cols-12 gap-8">

            {{-- Brand --}}
            <div class="col-span-2 md:col-span-6">
                <a href="{{ route('home') }}" class="inline-block mb-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded">
                    <span class="font-display text-lg font-bold tracking-[0.18em] uppercase text-ink">MH Scarf</span>
                </a>
                <p class="text-muted text-sm leading-relaxed max-w-sm">
                    Hijab dengan material premium dan sentuhan feminin, dibuat untuk momen sehari-hari Anda.
                </p>
            </div>

            {{-- Shop --}}
            <div class="md:col-span-3">
                <h3 class="text-[11px] font-semibold uppercase tracking-widest text-muted mb-3">Belanja</h3>
                <ul class="space-y-2 text-sm text-ink">
                    <li><a href="{{ route('shop.index') }}" class="hover:text-pink-deep transition">Semua Produk</a></li>
                    <li><a href="{{ route('shop.category', 'pashmina') }}" class="hover:text-pink-deep transition">Pashmina</a></li>
                    <li><a href="{{ route('shop.category', 'segi-empat') }}" class="hover:text-pink-deep transition">Segi Empat</a></li>
                    <li><a href="{{ route('shop.category', 'hijab-instant') }}" class="hover:text-pink-deep transition">Hijab Instant</a></li>
                    <li><a href="{{ route('shop.category', 'hijab-premium') }}" class="hover:text-pink-deep transition">Hijab Premium</a></li>
                </ul>
            </div>

            {{-- Customer help --}}
            <div class="md:col-span-3">
                <h3 class="text-[11px] font-semibold uppercase tracking-widest text-muted mb-3">Bantuan</h3>
                <ul class="space-y-2 text-sm text-ink">
                    <li><a href="{{ route('orders.index') }}" class="hover:text-pink-deep transition">Lacak Pesanan</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:text-pink-deep transition">FAQ</a></li>
                    @auth
                        <li><a href="{{ route('wishlist.index') }}" class="hover:text-pink-deep transition">Wishlist Saya</a></li>
                    @endauth
                </ul>
            </div>
        </div>

        {{-- Bottom row: copyright + social --}}
        <div class="mt-8 pt-5 border-t border-line flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-muted">© {{ date('Y') }} MH Scarf Store · Elegance in Every Wrap</p>

            <div class="flex items-center gap-1 text-muted">
                <a href="https://instagram.com" target="_blank" rel="noopener noreferrer"
                    class="hover:text-pink-deep transition w-9 h-9 inline-flex items-center justify-center rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep"
                    aria-label="Instagram MH Scarf Store">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="0.9" fill="currentColor" stroke="none"/>
                    </svg>
                </a>
                <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer"
                    class="hover:text-pink-deep transition w-9 h-9 inline-flex items-center justify-center rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep"
                    aria-label="TikTok MH Scarf Store">
                    <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M16.6 5.82A4.28 4.28 0 0 1 15.54 3h-3.09v12.4a2.59 2.59 0 1 1-1.86-2.48V7.77a5.74 5.74 0 1 0 4.95 5.68V9.01a7.35 7.35 0 0 0 4.3 1.38V7.3a4.29 4.29 0 0 1-2.38-1.48Z"/>
                    </svg>
                </a>
                <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer"
                    class="hover:text-pink-deep transition w-9 h-9 inline-flex items-center justify-center rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep"
                    aria-label="WhatsApp MH Scarf Store">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</footer>