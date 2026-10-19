<x-layouts.app>
    @section('title', 'Riwayat Pesanan — MH Scarf')

    {{-- Stage 6: migrated to shared app layout + design tokens.
         Backend contract unchanged: orders.index (GET) / orders.show (GET). --}}

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="text-xs text-muted mb-6">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded transition">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('profile') }}" class="hover:text-pink-deep transition">Akun</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-ink font-medium">Pesanan Saya</li>
            </ol>
        </nav>

        {{-- Page header --}}
        <div class="mb-8">
            <p class="text-[11px] uppercase tracking-[0.2em] text-pink-mauve font-semibold mb-1">Riwayat Belanja</p>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink">Pesanan Saya</h1>
            <p class="text-sm text-muted mt-1.5">Lacak dan pantau status pemesanan hijab dan aksesori Anda.</p>
        </div>

        @if($orders->isEmpty())
            {{-- Empty state --}}
            <div class="bg-white rounded-2xl border border-line p-10 sm:p-14 text-center shadow-card">
                <div class="w-16 h-16 mx-auto rounded-full bg-cream border border-line flex items-center justify-center text-2xl text-pink-deep mb-5">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h2 class="font-display text-xl font-bold text-ink mb-2">Belum Ada Riwayat Pesanan</h2>
                <p class="text-sm text-muted max-w-md mx-auto mb-6">
                    Anda belum pernah membuat pesanan di MH Scarf. Jelajahi katalog hijab anggun kami dan mulai berbelanja hari ini!
                </p>
                <a href="{{ route('shop.index') }}"
                    class="inline-flex items-center px-6 py-2.5 bg-pink-deep hover:bg-pink-mauve text-white text-sm font-medium rounded-xl transition shadow-card hover:shadow-card-hover">
                    Lihat Koleksi Produk
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($orders as $order)
                    <article class="bg-white rounded-2xl border border-line p-5 sm:p-6 shadow-card hover:shadow-card-hover hover:border-pink-deep/40 transition-all duration-300">
                        {{-- Header Row --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-line">
                            <div>
                                <div class="flex items-center space-x-3 flex-wrap gap-y-1">
                                    <span class="font-mono text-sm font-bold text-ink">{{ $order->order_number }}</span>
                                    <span class="text-xs text-muted">
                                        {{ $order->placed_at ? $order->placed_at->format('d M Y, H:i') : $order->created_at->format('d M Y, H:i') }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                {{-- Order Status Badge --}}
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $order->statusBadgeClass() }}">
                                    {{ $order->statusLabel() }}
                                </span>
                                {{-- Payment Status Badge --}}
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $order->paymentStatusBadgeClass() }}">
                                    {{ $order->paymentStatusLabel() }}
                                </span>
                            </div>
                        </div>

                        {{-- Items Overview --}}
                        <div class="py-4 space-y-3">
                            @foreach($order->items->take(2) as $item)
                                @php
                                    $primaryImage = $item->product?->primaryImage();
                                @endphp
                                <div class="flex items-center justify-between gap-4 text-xs">
                                    <div class="flex items-center space-x-3 min-w-0">
                                        <div class="w-11 h-11 rounded-xl bg-cream border border-line overflow-hidden shrink-0 flex items-center justify-center">
                                            @if($primaryImage)
                                                <img src="{{ $primaryImage->image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-5 h-5 text-pink-deep" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-ink truncate">{{ $item->product_name }}</p>
                                            <p class="text-muted">Varian: {{ $item->variant_name }} ({{ $item->quantity }} pcs)</p>
                                        </div>
                                    </div>
                                    <span class="font-medium text-ink shrink-0">{{ $item->formattedSubtotal() }}</span>
                                </div>
                            @endforeach

                            @if($order->items->count() > 2)
                                <p class="text-[11px] text-muted italic">
                                    + {{ $order->items->count() - 2 }} produk lainnya
                                </p>
                            @endif
                        </div>

                        {{-- Ajakan ulasan: hanya untuk pesanan yang sudah tiba dan masih punya produk belum diulas --}}
@php $unreviewed = $order->items->filter(fn ($i) => $i->review === null)->count(); @endphp
@if ($order->isReviewable() && $unreviewed > 0)
    <a href="{{ route('orders.show', $order) }}"
        class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-pink-deep/30 bg-pink-soft/30 px-4 py-3 transition hover:bg-pink-soft/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
        <span class="text-xs text-ink">
            <strong class="font-semibold">Pesanan sudah tiba.</strong>
            Beri ulasan untuk {{ $unreviewed }} produk.
        </span>
        <span class="shrink-0 text-gold tracking-widest" aria-hidden="true">★★★★★</span>
    </a>
@endif

                        {{-- Footer Row --}}
                        <div class="pt-4 border-t border-line flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="text-xs text-muted">
                                Total Pesanan ({{ $order->items->sum('quantity') }} produk):
                                <span class="text-base font-bold text-pink-deep ml-1">{{ $order->formattedGrandTotal() }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($order->status === 'pending' && $order->payment_status !== 'paid')
                                    <a href="{{ route('payments.show', $order) }}"
                                        class="inline-flex items-center px-4 py-2 bg-pink-deep hover:bg-pink-mauve text-white text-xs font-medium rounded-xl transition shadow-card">
                                        Bayar Sekarang
                                    </a>
                                @endif
                                <a href="{{ route('orders.show', $order) }}"
                                    class="inline-flex items-center px-4 py-2 bg-cream hover:bg-pink-deep hover:text-white border border-line text-ink text-xs font-medium rounded-xl transition">
                                    Lihat Detail →
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach

                {{-- Pagination --}}
                <div class="pt-4">
                    {{ $orders->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
