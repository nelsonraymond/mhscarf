<x-confirm-dialog />
<x-layouts.app>
    @section('title', 'Keranjang Belanja — MH Scarf')

    {{-- Tahap 4: migrated to shared app layout (navbar/footer/flash), design tokens,
         stepper qty consistent with product detail, server-side max/min enforced,
         sticky mobile summary bar. Routes & payload names unchanged:
         cart.items.update (PATCH, quantity) / cart.items.destroy (DELETE) / cart.destroy (DELETE). --}}

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 pb-28 lg:pb-12">

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="text-xs text-muted mb-6">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded transition">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-ink font-medium">Keranjang Belanja</li>
            </ol>
        </nav>

        <div class="mb-8">
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink">Keranjang Belanja</h1>
            <p class="text-sm text-muted mt-1.5">Periksa kembali pilihan hijab favorit Anda sebelum melanjutkan.</p>
        </div>

        {{-- Field-level validation errors from CartService (e.g. quantity melebihi stok) --}}
        @if ($errors->any())
            <div role="alert" aria-live="assertive" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm text-rose-800">
                <p class="font-semibold mb-1">Periksa kembali:</p>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! $cart || $cart->items->isEmpty())
            {{-- Empty state --}}
            <div class="bg-white rounded-2xl border border-line p-10 sm:p-12 text-center max-w-lg mx-auto shadow-card">
                <div class="w-16 h-16 rounded-full bg-pink-soft/30 text-pink-deep flex items-center justify-center mx-auto mb-4" aria-hidden="true">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                    </svg>
                </div>
                <h2 class="font-display text-xl font-bold text-ink">Keranjang Anda Masih Kosong</h2>
                <p class="text-sm text-muted mt-1.5 mb-6">Jelajahi koleksi hijab kami dan temukan pilihan anggun favorit Anda.</p>
                <a href="{{ route('shop.index') }}"
                    class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-pink-deep hover:bg-pink-mauve text-white font-medium text-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-pink-deep">
                    Mulai Belanja Hijab
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-start">

                {{-- ================= CART ITEMS ================= --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl border border-line overflow-hidden shadow-card">
                        <div class="p-4 bg-surface border-b border-line flex justify-between items-center text-xs font-semibold text-muted uppercase tracking-wide">
                            <span>Item Produk ({{ $cart->totalItems() }} pcs)</span>
                            <form method="POST" action="{{ route('cart.destroy') }}"
                            adata-confirm="Semua produk di keranjang akan dihapus. Tindakan ini tidak dapat dibatalkan."
    data-confirm-title="Kosongkan keranjang?"
    data-confirm-ok="Ya, Kosongkan">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-xs text-muted hover:text-pink-deep transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded">
                                    Kosongkan Keranjang
                                </button>
                            </form>
                        </div>

                        <ul class="divide-y divide-line list-none m-0 p-0">
                            @foreach ($cart->items as $item)
                                @php
                                    $variant = $item->variant;
                                    $product = $variant?->product;
                                    $img = $product?->primaryImage();
                                    $stockMax = max(1, (int) ($variant->stock_qty ?? 1));
                                @endphp
                                <li class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 min-w-0">
                                    {{-- Product meta --}}
                                    <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                                        @if ($img)
                                            <img src="{{ Storage::url($img->image_path) }}" alt="{{ $product->name }}"
                                                loading="lazy"
                                                class="w-16 h-20 object-cover rounded-xl border border-line bg-cream shrink-0">
                                        @else
                                            <div class="w-16 h-20 rounded-xl bg-pink-soft/20 border border-line flex items-center justify-center text-[10px] tracking-widest text-pink-deep uppercase shrink-0" aria-hidden="true">
                                                MH Scarf
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            @if ($product->category)
                                                <span class="text-[10px] text-muted uppercase tracking-wider block">{{ $product->category->name }}</span>
                                            @endif
                                            <a href="{{ route('shop.product', $product->slug) }}"
                                                class="font-medium text-sm text-ink hover:text-pink-deep transition line-clamp-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded">
                                                {{ $product->name }}
                                            </a>
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-muted mt-1.5">
                                                @if ($variant->color_hex)
                                                    <span class="w-3 h-3 rounded-full border border-line shrink-0" style="background-color: {{ $variant->color_hex }}" aria-hidden="true"></span>
                                                @endif
                                                <span>{{ $variant->name }}</span>
                                                <span aria-hidden="true">•</span>
                                                <span>{{ $variant->size ?? 'All Size' }}</span>
                                            </div>
                                            <div class="text-xs font-semibold text-ink mt-1.5">
                                                Rp{{ number_format($item->unit_price, 0, ',', '.') }}
                                                <span class="font-normal text-muted">/ pcs</span>
                                            </div>
                                            @if ($item->quantity >= $variant->stock_qty)
                                                <p class="text-[11px] text-pink-mauve mt-1">Kuantitas sudah maksimum sesuai stok ({{ $variant->stock_qty }} pcs).</p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Qty stepper + subtotal + delete. Each form standalone (no nesting). --}}
                                    <div class="flex items-center justify-between sm:justify-end gap-4 w-full sm:w-auto">
                                        {{-- Single standalone form; − / + are submit buttons carrying
                                             their own quantity value, so no JS is strictly required and
                                             there is never a form inside a form. --}}
                                        <form method="POST" action="{{ route('cart.items.update', $item) }}"
                                            data-cart-form
                                            class="flex items-center gap-3 shrink-0">
                                            @csrf
                                            @method('PATCH')

                                            <div class="flex items-center border border-line rounded-xl overflow-hidden bg-white shrink-0">
    <button type="button" data-step="-1"
        {{ $item->quantity <= 1 ? 'disabled' : '' }}
        aria-label="Kurangi kuantitas {{ $product->name }}"
        class="w-11 h-11 text-ink hover:bg-cream text-base focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-pink-deep disabled:opacity-40 disabled:cursor-not-allowed transition">−</button>

    <label class="sr-only" for="qty-{{ $item->id }}">Kuantitas {{ $product->name }}</label>
    <input type="number"
        id="qty-{{ $item->id }}"
        name="quantity"
        value="{{ $item->quantity }}"
        min="1"
        max="{{ $stockMax }}"
        step="1"
        required
        class="w-14 h-11 text-center text-sm text-ink border-x border-line bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-pink-deep [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none">

    <button type="button" data-step="1"
        {{ $item->quantity >= $stockMax ? 'disabled' : '' }}
        aria-label="Tambah kuantitas {{ $product->name }}"
        class="w-11 h-11 text-ink hover:bg-cream text-base focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-pink-deep disabled:opacity-40 disabled:cursor-not-allowed transition">+</button>
</div>
                                        </form>

                                        <div class="text-right shrink-0">
                                            <span class="text-sm font-bold text-ink block">
                                                Rp{{ number_format($item->subtotal(), 0, ',', '.') }}
                                            </span>
                                            <form method="POST" action="{{ route('cart.items.destroy', $item) }}" class="inline mt-0.5"
    data-confirm="{{ $product->name }} ({{ $variant->name }}) akan dihapus dari keranjang Anda."
    data-confirm-title="Hapus produk ini?"
    data-confirm-ok="Ya, Hapus">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-xs text-muted hover:text-pink-deep transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <a href="{{ route('shop.index') }}"
                        class="inline-flex items-center gap-1.5 mt-4 text-sm text-muted hover:text-pink-deep transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded px-1 py-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        Lanjut berbelanja di katalog
                    </a>
                </div>

                {{-- ================= SUMMARY (desktop) ================= --}}
                <aside class="hidden lg:block lg:sticky lg:top-24 space-y-4" aria-label="Ringkasan belanja">
                    <div class="bg-white rounded-2xl border border-line p-6 shadow-card space-y-4">
                        <h2 class="font-display text-lg font-bold text-ink border-b border-line pb-3">Ringkasan Belanja</h2>

                        <div class="space-y-2 text-sm text-muted">
                            <div class="flex justify-between">
                                <span>Total Item</span>
                                <span class="font-medium text-ink">{{ $cart->totalItems() }} pcs</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Subtotal Produk</span>
                                <span class="font-semibold text-ink">Rp{{ number_format($cart->subtotal(), 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Biaya Pengiriman</span>
                                <span class="text-emerald-700">Dihitung saat checkout</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-line flex justify-between items-baseline">
                            <span class="text-sm font-bold text-ink">Total Subtotal</span>
                            <span class="text-xl font-bold text-pink-deep font-display">Rp{{ number_format($cart->subtotal(), 0, ',', '.') }}</span>
                        </div>

                        <a href="{{ route('checkout.index') }}"
                            class="block w-full py-3 rounded-xl bg-pink-deep hover:bg-pink-mauve text-white font-medium text-sm transition text-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-pink-deep">
                            Lanjut ke Checkout
                        </a>
                    </div>
                </aside>
            </div>

            {{-- Sticky summary bar (mobile/tablet) --}}
            <div class="fixed bottom-0 inset-x-0 z-50 lg:hidden bg-white border-t border-line shadow-[0_-4px_16px_rgba(58,48,51,0.08)]">
                <div class="px-4 py-3 flex items-center justify-between gap-4 max-w-7xl mx-auto">
                    <div>
                        <p class="text-[11px] text-muted leading-tight">Subtotal ({{ $cart->totalItems() }} pcs)</p>
                        <p class="text-base font-bold text-ink leading-tight font-display">Rp{{ number_format($cart->subtotal(), 0, ',', '.') }}</p>
                    </div>
                    <a href="{{ route('checkout.index') }}"
                        class="shrink-0 inline-flex items-center justify-center px-5 h-12 rounded-xl bg-pink-deep text-white font-medium text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-pink-deep active:bg-pink-mauve transition">
                        Checkout
                    </a>
                </div>
            </div>
        @endif
    </div>

    <script>
    document.querySelectorAll('[data-cart-form]').forEach(function (form) {
        var input = form.querySelector('input[type="number"]');
        if (!input) return;

        var min = parseInt(input.min, 10) || 1;
        var max = parseInt(input.max, 10) || min;

        function clamp(v) {
            return isNaN(v) ? min : Math.min(Math.max(v, min), max);
        }

        // Tombol − / +: ubah nilai input, lalu submit
        form.querySelectorAll('[data-step]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var current = clamp(parseInt(input.value, 10));
                var next = clamp(current + parseInt(btn.dataset.step, 10));
                if (next === current) return;
                input.value = next;
                form.requestSubmit();
            });
        });

        // Ketik manual: rapikan nilainya sebelum dikirim
        form.addEventListener('submit', function () {
            input.value = clamp(parseInt(input.value, 10));
        });
    });
</script>
</x-layouts.app>
