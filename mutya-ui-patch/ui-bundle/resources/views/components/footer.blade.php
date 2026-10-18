{{-- Footer — DESIGN.md §26: brand, navigation, customer service, social, newsletter + subtle floral edge. --}}
<footer class="mt-16 bg-white border-t border-line relative overflow-hidden">
    {{-- Subtle floral line-art background edge (§6: opacity 10–35%) --}}
    <svg class="absolute -bottom-6 -right-6 w-48 h-48 text-pink-soft opacity-30 pointer-events-none" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true">
        <path d="M50 90 C50 60 50 40 50 20"/>
        <path d="M50 55 C35 50 28 38 30 25 C45 30 52 42 50 55Z"/>
        <path d="M50 45 C65 40 72 28 70 15 C55 20 48 32 50 45Z"/>
        <circle cx="50" cy="16" r="5"/><circle cx="42" cy="12" r="4"/><circle cx="58" cy="12" r="4"/>
    </svg>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16 grid grid-cols-2 md:grid-cols-4 gap-8 text-sm relative">
        {{-- Brand --}}
        <div class="col-span-2 md:col-span-1">
            <a href="{{ route('home') }}" class="inline-flex items-center space-x-2 mb-3">
                <span class="font-display text-lg font-bold tracking-[0.18em] uppercase text-ink">MH Scarf</span>
            </a>
            <p class="text-muted text-xs leading-relaxed max-w-xs">Hijab dengan material premium dan sentuhan feminin — dibuat untuk momen sehari-hari Anda.</p>
            <div class="flex gap-3 mt-4 text-muted">
                <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="hover:text-pink-deep transition p-1 min-h-[36px] inline-flex items-center" aria-label="Instagram MH Scarf">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="0.9" fill="currentColor" stroke="none"/>
                    </svg>
                </a>
                <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer" class="hover:text-pink-deep transition p-1 min-h-[36px] inline-flex items-center" aria-label="TikTok MH Scarf">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M16.6 5.82A4.28 4.28 0 0 1 15.54 3h-3.09v12.4a2.59 2.59 0 1 1-1.86-2.48V7.77a5.74 5.74 0 1 0 4.95 5.68V9.01a7.35 7.35 0 0 0 4.3 1.38V7.3a4.29 4.29 0 0 1-2.38-1.48Z"/>
                    </svg>
                </a>
                <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer" class="hover:text-pink-deep transition p-1 min-h-[36px] inline-flex items-center" aria-label="WhatsApp MH Scarf">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- Shop --}}
        <div>
            <h3 class="font-display font-semibold text-ink mb-3">Belanja</h3>
            <ul class="space-y-2 text-xs text-muted">
                <li><a href="{{ route('shop.index') }}" class="hover:text-pink-deep transition">Semua Produk</a></li>
                <li><a href="{{ route('shop.category', 'pashmina') }}" class="hover:text-pink-deep transition">Pashmina</a></li>
                <li><a href="{{ route('shop.category', 'segi-empat') }}" class="hover:text-pink-deep transition">Segi Empat</a></li>
                <li><a href="{{ route('shop.category', 'hijab-instant') }}" class="hover:text-pink-deep transition">Hijab Instant</a></li>
                <li><a href="{{ route('shop.category', 'hijab-premium') }}" class="hover:text-pink-deep transition">Hijab Premium</a></li>
            </ul>
        </div>

        {{-- Customer help --}}
        <div>
            <h3 class="font-display font-semibold text-ink mb-3">Bantuan Pelanggan</h3>
            <ul class="space-y-2 text-xs text-muted">
                <li><a href="{{ route('orders.index') }}" class="hover:text-pink-deep transition">Lacak Pesanan</a></li>
                <li><a href="{{ route('home') }}#pengiriman" class="hover:text-pink-deep transition">Pengiriman</a></li>
                <li><a href="{{ route('home') }}#pengembalian" class="hover:text-pink-deep transition">Pengembalian</a></li>
                <li><a href="{{ route('home') }}#faq" class="hover:text-pink-deep transition">FAQ</a></li>
                @auth
                    <li><a href="{{ route('wishlist.index') }}" class="hover:text-pink-deep transition">Wishlist Saya</a></li>
                @endauth
            </ul>
        </div>

        {{-- Newsletter --}}
        <div class="col-span-2 md:col-span-1">
            <h3 class="font-display font-semibold text-ink mb-3">Dapat 10% OFF Order Pertama</h3>
            <form class="flex" action="#" method="POST" onsubmit="event.preventDefault(); this.querySelector('button').textContent='Terima kasih'; ">
                @csrf
                <label for="footer-newsletter-email" class="sr-only">Alamat email</label>
                <input id="footer-newsletter-email" type="email" required placeholder="Email Anda"
                    class="flex-1 min-w-0 px-3 py-2 rounded-l-xl border border-line bg-cream text-xs focus:outline-none focus:ring-1 focus:ring-pink-deep">
                <button type="submit" class="px-4 py-2 rounded-r-xl bg-pink-deep hover:bg-pink-mauve text-white text-xs font-medium transition cursor-pointer">Subscribe</button>
            </form>
            <p class="text-[11px] text-muted mt-2">Promo & koleksi terbaru, tanpa spam.</p>
        </div>
    </div>

    <div class="border-t border-line py-4 text-center text-[11px] text-muted">
        © {{ date('Y') }} MH Scarf · Elegance in Every Wrap
    </div>
</footer>
