<x-layouts.app>
    @section('title', 'Detail Pesanan #' . $order->order_number . ' — MH Scarf Store')

    {{-- Stage 6: migrated to shared app layout + design tokens.
         Backend contract unchanged: orders.show (GET) / orders.cancel (POST). --}}

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="text-xs text-muted mb-6">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded transition">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('orders.index') }}" class="hover:text-pink-deep transition">Pesanan Saya</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-ink font-medium">#{{ $order->order_number }}</li>
            </ol>
        </nav>

        {{-- Order Header Card --}}
        <div class="bg-white rounded-2xl border border-line p-6 shadow-card mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-line">
                <div>
                    <div class="flex items-center space-x-2 text-xs text-muted mb-1">
                        <span>Waktu Pesan:</span>
                        <span class="text-ink font-medium">
                            {{ $order->placed_at ? $order->placed_at->format('d M Y, H:i') : $order->created_at->format('d M Y, H:i') }}
                        </span>
                    </div>
                    <h1 class="font-display text-xl sm:text-2xl font-bold text-ink">
                        Pesanan <span class="font-mono text-pink-deep">#{{ $order->order_number }}</span>
                    </h1>
                </div>

                <div class="flex items-center space-x-2 flex-wrap gap-y-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $order->statusBadgeClass() }}">
                        Status: {{ $order->statusLabel() }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $order->paymentStatusBadgeClass() }}">
                        Pembayaran: {{ $order->paymentStatusLabel() }}
                    </span>
                </div>
            </div>

            {{-- Visual Order Tracking Timeline --}}
            <div class="pt-6">
                <h2 class="text-xs font-semibold text-muted uppercase tracking-wider mb-5">
                    Lacak Perjalanan Pesanan
                </h2>

                @if($order->isCancelled())
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                        <p class="font-semibold text-rose-900 mb-1">✕ Pesanan Ini Telah Dibatalkan</p>
                        <p>Pesanan telah dibatalkan dan tidak lagi diproses. Stok telah dikembalikan ke inventaris.</p>
                    </div>
                @else
                    {{-- Stepper Timeline --}}
                    <div class="relative">
                        {{-- Horizontal Track (desktop) --}}
                        <div class="hidden md:flex justify-between gap-1 relative overflow-hidden py-1">
                            {{-- Background connecting bar --}}
                            <div class="absolute top-4 left-[calc(100%/14)] right-[calc(100%/14)] h-0.5 bg-line -z-0"></div>

                            @foreach($order->trackingTimeline() as $step)
                                <div class="flex flex-col items-center text-center relative z-10 flex-1 min-w-0 px-1">
                                    <div class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-xs font-bold transition {{ $step['is_completed'] ? 'bg-pink-deep text-white shadow-card' : 'bg-white border-2 border-line text-muted' }} {{ $step['is_current'] ? 'ring-4 ring-pink-deep/25' : '' }}">
                                        @if($step['is_completed'] && ! $step['is_current'])
                                            ✓
                                        @else
                                            {{ $loop->iteration }}
                                        @endif
                                    </div>
                                    <span class="text-xs font-semibold mt-2 leading-snug w-full break-words {{ $step['is_completed'] ? 'text-ink' : 'text-muted' }}">
                                        {{ $step['title'] }}
                                    </span>
                                    <span class="text-[10px] text-muted mt-0.5 leading-tight w-full break-words">
                                        {{ $step['description'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Vertical Track (mobile) --}}
                        <div class="md:hidden space-y-4 relative pl-6 before:content-[''] before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-line">
                            @foreach($order->trackingTimeline() as $step)
                                <div class="relative flex items-start space-x-3">
                                    <div class="absolute -left-6 top-0.5 w-5 h-5 shrink-0 rounded-full flex items-center justify-center text-[10px] font-bold {{ $step['is_completed'] ? 'bg-pink-deep text-white' : 'bg-white border border-line text-muted' }} {{ $step['is_current'] ? 'ring-2 ring-pink-deep/30' : '' }}">
                                        @if($step['is_completed'] && ! $step['is_current'])
                                            ✓
                                        @else
                                            {{ $loop->iteration }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold leading-snug break-words {{ $step['is_completed'] ? 'text-ink' : 'text-muted' }}">
                                            {{ $step['title'] }}
                                        </p>
                                        <p class="text-[11px] text-muted leading-snug break-words">{{ $step['description'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Left: Order Items & Delivery Info --}}
            <div class="lg:col-span-8 space-y-6">
                {{-- Purchased Items Snapshot Card --}}
                <div class="bg-white rounded-2xl border border-line p-6 shadow-card">
                    <h2 class="font-display text-base font-bold text-ink pb-3 border-b border-line mb-4">
                        Daftar Produk yang Dipesan
                    </h2>

                    <div class="divide-y divide-line">
                        @foreach($order->items as $item)
                            @php
                                $primaryImage = $item->product?->primaryImage();
                            @endphp
                            <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                                <div class="flex items-center space-x-3.5 min-w-0">
                                    <div class="w-14 h-14 rounded-xl bg-cream border border-line overflow-hidden shrink-0 flex items-center justify-center">
                                        @if($primaryImage)
                                            <img src="{{ asset('storage/' . $primaryImage->image_path) }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-6 h-6 text-pink-deep" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-medium text-sm text-ink truncate">{{ $item->product_name }}</h3>
                                        <div class="flex items-center space-x-2 text-xs text-muted mt-0.5 flex-wrap">
                                            <span>Varian: <strong class="text-ink">{{ $item->variant_name }}</strong></span>
                                            <span>•</span>
                                            <span class="font-mono text-[11px]">{{ $item->sku }}</span>
                                        </div>
                                        <p class="text-xs text-muted mt-0.5">
                                            {{ $item->quantity }} x {{ $item->formattedUnitPrice() }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-semibold text-sm text-ink">
                                        {{ $item->formattedSubtotal() }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Shipping Address Card --}}
                <div class="bg-white rounded-2xl border border-line p-6 shadow-card">
                    <h2 class="font-display text-base font-bold text-ink pb-3 border-b border-line mb-4">
                        Alamat Pengiriman
                    </h2>

                    <div class="text-xs space-y-2">
                        <div class="flex items-baseline space-x-2">
                            <span class="font-semibold text-sm text-ink">{{ $order->shipping_recipient_name }}</span>
                            <span class="text-muted font-mono">({{ $order->shipping_phone }})</span>
                        </div>
                        <p class="text-sm text-ink leading-relaxed">
                            {{ $order->shipping_address }}
                        </p>
                        <p class="text-muted">
                            {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}
                        </p>

                        @if($order->customer_note)
                            <div class="mt-4 p-3 rounded-xl bg-cream border border-line text-xs text-muted">
                                <strong class="text-ink block mb-0.5">Catatan Pesanan dari Anda:</strong>
                                {{ $order->customer_note }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Shipping & Price Breakdown --}}
            <div class="lg:col-span-4 space-y-6">
                {{-- Shipment Card --}}
                <div class="bg-white rounded-2xl border border-line p-6 shadow-card">
                    <h2 class="font-display text-base font-bold text-ink pb-3 border-b border-line mb-4">
                        Informasi Ekspedisi
                    </h2>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between">
                            <span class="text-muted">Kurir:</span>
                            <span class="font-medium text-ink">{{ $order->shipment?->courier ?? 'MH Scarf Express Delivery' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">Layanan:</span>
                            <span class="font-medium text-ink">{{ $order->shipment?->service ?? 'Reguler' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">No. Resi:</span>
                            <span class="font-mono font-medium text-ink">
                                {{ $order->shipment?->tracking_number ?? 'Belum diterbitkan' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">Status Pengiriman:</span>
                            <span class="font-medium text-pink-deep uppercase">
                                {{ $order->shipment?->status ?? 'Menunggu Pengiriman' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Price Summary Card --}}
                <div class="bg-white rounded-2xl border border-line p-6 shadow-card space-y-3 text-xs">
                    <h2 class="font-display text-base font-bold text-ink pb-3 border-b border-line">
                        Rincian Pembayaran
                    </h2>

                    <div class="flex justify-between text-muted">
                        <span>Subtotal Produk</span>
                        <span class="font-medium text-ink">{{ $order->formattedSubtotal() }}</span>
                    </div>

                    <div class="flex justify-between text-muted">
                        <span>Biaya Pengiriman</span>
                        <span class="font-medium text-ink">{{ $order->formattedShippingCost() }}</span>
                    </div>

                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-emerald-600">
                            <span>Diskon Voucher</span>
                            <span class="font-medium">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-line flex justify-between items-baseline text-sm">
                        <span class="font-bold text-ink">Total Akhir</span>
                        <span class="font-display text-lg font-bold text-pink-deep">
                            {{ $order->formattedGrandTotal() }}
                        </span>
                    </div>
                </div>

                {{-- Payment Action --}}
                @if($order->status === 'pending' && $order->payment_status !== 'paid')
                    <a href="{{ route('payments.show', $order) }}"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-pink-deep hover:bg-pink-mauve text-white text-sm font-semibold transition shadow-card hover:shadow-card-hover min-h-[44px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                        </svg>
                        Lanjutkan Pembayaran
                    </a>
                @endif

                {{-- Cancellation Card --}}
                @if($order->isCancellable())
                    <div class="bg-white rounded-2xl border border-line p-6 shadow-card">
                        <h2 class="font-display text-base font-bold text-ink pb-3 border-b border-line mb-4">
                            Perlu Membatalkan?
                        </h2>

                        <p class="text-xs text-muted leading-relaxed mb-4">
                            Pembatalan hanya tersedia sebelum pesanan dikirim. Stok produk akan otomatis dikembalikan setelah pesanan dibatalkan.
                        </p>

                        <form action="{{ route('orders.cancel', $order) }}" method="POST"
                            onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?');">
                            @csrf
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 text-sm font-semibold hover:bg-rose-100 hover:border-rose-300 active:bg-rose-200 transition min-h-[44px]">
                                <span aria-hidden="true">✕</span>
                                Batalkan Pesanan
                            </button>
                        </form>
                    </div>
                @endif

                <div class="text-center pt-2 space-y-2">
                    <a href="{{ route('orders.index') }}"
                        class="block text-xs text-muted hover:text-pink-deep font-medium transition">
                        ← Kembali ke Riwayat Pesanan
                    </a>
                    <a href="{{ route('shop.index') }}"
                        class="block text-xs text-muted hover:text-pink-deep font-medium transition">
                        Lanjutkan Berbelanja
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
