<x-layouts.app>
    @section('title', 'Pembayaran Pesanan #' . $order->order_number . ' — MH Scarf Store')

    {{-- Stage 6: migrated to shared app layout + design tokens.
         Backend contract unchanged: payments.show (GET) / payments.simulate (POST). --}}

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="text-xs text-muted mb-6">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded transition">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('orders.index') }}" class="hover:text-pink-deep transition">Pesanan Saya</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('orders.show', $order) }}" class="hover:text-pink-deep transition">#{{ $order->order_number }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-ink font-medium">Pembayaran</li>
            </ol>
        </nav>

        {{-- Header Card --}}
        <div class="bg-white rounded-2xl border border-line p-6 shadow-card mb-6">
            <p class="text-[11px] uppercase tracking-[0.2em] text-pink-mauve font-semibold mb-1">Halaman Pembayaran</p>
            <h1 class="font-display text-xl sm:text-2xl font-bold text-ink mb-3">
                Pesanan <span class="font-mono text-pink-deep">#{{ $order->order_number }}</span>
            </h1>
            <div class="flex items-center space-x-2 flex-wrap gap-y-2">
                <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $order->statusBadgeClass() }}">
                    Status Pesanan: {{ $order->statusLabel() }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $order->paymentStatusBadgeClass() }}">
                    Pembayaran: {{ $order->paymentStatusLabel() }}
                </span>
            </div>
        </div>

        {{-- Payment Detail Card --}}
        <div class="bg-white rounded-2xl border border-line p-6 shadow-card mb-6">
            <h2 class="font-display text-lg font-semibold text-ink mb-4">Informasi Pembayaran</h2>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <dt class="text-muted text-xs uppercase tracking-wide mb-0.5">Metode Pembayaran</dt>
                    <dd class="text-ink font-medium">{{ $payment?->payment_method ?? 'midtrans_snap' }}</dd>
                </div>
                <div>
                    <dt class="text-muted text-xs uppercase tracking-wide mb-0.5">Penyedia</dt>
                    <dd class="text-ink font-medium">{{ ucfirst($payment?->provider ?? 'midtrans') }} (Sandbox)</dd>
                </div>
                <div>
                    <dt class="text-muted text-xs uppercase tracking-wide mb-0.5">Status Pembayaran</dt>
                    <dd>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $payment?->statusBadgeClass() ?? $order->paymentStatusBadgeClass() }}">
                            {{ $payment?->statusLabel() ?? $order->paymentStatusLabel() }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-muted text-xs uppercase tracking-wide mb-0.5">Referensi Transaksi</dt>
                    <dd class="text-ink font-mono text-xs break-all">{{ $payment?->transaction_reference ?? '— akan dibuat saat pembayaran diproses —' }}</dd>
                </div>
                @if($payment?->paid_at)
                    <div>
                        <dt class="text-muted text-xs uppercase tracking-wide mb-0.5">Waktu Bayar</dt>
                        <dd class="text-ink font-medium">{{ $payment->paid_at->format('d M Y, H:i') }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-muted text-xs uppercase tracking-wide mb-0.5">Total Tagihan</dt>
                    <dd class="font-display text-xl font-bold text-pink-deep">{{ $order->formattedGrandTotal() }}</dd>
                </div>
            </dl>

            <div class="mt-5 pt-4 border-t border-line space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted">Subtotal produk</span>
                    <span class="text-ink">{{ $order->formattedSubtotal() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Ongkos kirim</span>
                    <span class="text-ink">{{ $order->formattedShippingCost() }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-emerald-600">
                        <span>Diskon Voucher</span>
                        <span class="font-medium">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-semibold">
                    <span class="text-ink">Grand total</span>
                    <span class="text-ink">{{ $order->formattedGrandTotal() }}</span>
                </div>
            </div>
        </div>

        {{-- Instructions Card --}}
        <div class="bg-cream rounded-2xl border border-line p-6 mb-6">
            <h2 class="font-display text-base font-semibold text-ink mb-2">Instruksi Pembayaran</h2>
            @if($order->payment_status === 'paid')
                <div class="flex items-start gap-2 text-sm text-emerald-700">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>Pembayaran sudah diterima. Pesanan Anda sedang kami proses. Pantau statusnya di halaman detail pesanan.</p>
                </div>
            @elseif($order->payment_status === 'failed')
                <div class="flex items-start gap-2 text-sm text-rose-700">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    <p>Pembayaran gagal diproses. Silakan coba lagi atau hubungi customer service kami untuk bantuan.</p>
                </div>
            @elseif($order->payment_status === 'expired')
                <div class="flex items-start gap-2 text-sm text-amber-700">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>Batas waktu pembayaran telah habis. Silakan buat pesanan baru untuk melanjutkan pembelian.</p>
                </div>
            @elseif($order->status === 'cancelled')
                <div class="flex items-start gap-2 text-sm text-rose-700">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    <p>Pesanan ini sudah dibatalkan. Tidak ada pembayaran yang diperlukan.</p>
                </div>
            @else
                <ol class="list-decimal list-inside text-sm text-muted space-y-1.5">
                    <li>Lakukan pembayaran sebesar <span class="text-ink font-semibold">{{ $order->formattedGrandTotal() }}</span> melalui metode yang dipilih.</li>
                    <li>Status pesanan akan diperbarui otomatis setelah pembayaran terverifikasi oleh sistem kami.</li>
                    <li>Simpan referensi transaksi di atas bila diperlukan untuk konfirmasi manual.</li>
                </ol>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('orders.show', $order) }}"
                class="flex-1 inline-flex items-center justify-center px-4 py-3 rounded-xl font-medium text-sm border border-line bg-white text-ink hover:bg-cream transition shadow-card">
                Lihat Detail Pesanan
            </a>
            <a href="{{ route('orders.index') }}"
                class="flex-1 inline-flex items-center justify-center px-4 py-3 rounded-xl font-medium text-sm border border-line bg-white text-ink hover:bg-cream transition shadow-card">
                Riwayat Pesanan
            </a>
            <a href="{{ route('shop.index') }}"
                class="flex-1 inline-flex items-center justify-center px-4 py-3 rounded-xl font-medium text-sm text-muted hover:text-ink transition">
                Kembali ke Toko
            </a>
        </div>


@php
    $snapToken = $payment?->metadata['snap_token'] ?? null;
    $isFallback = $snapToken && \Illuminate\Support\Str::startsWith($snapToken, 'fallback_snap_token_');
    $canPay = $snapToken
        && ! $isFallback
        && $order->status === 'pending'
        && in_array($order->payment_status, ['pending', 'failed'], true);
    $isProduction = (bool) ($payment?->metadata['is_production'] ?? false);
    $clientKey = $payment?->metadata['client_key'] ?? config('midtrans.client_key');
@endphp

@if($canPay)
    <div class="mt-6">
        <button type="button" id="pay-button"
            class="w-full inline-flex items-center justify-center px-4 py-3 rounded-xl font-semibold text-sm bg-pink-deep text-white hover:opacity-90 transition shadow-card">
            Bayar Sekarang
        </button>
        <p id="pay-status" class="mt-3 text-sm text-muted text-center" aria-live="polite"></p>
    </div>

    <script src="{{ $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ $clientKey }}"></script>
    <script>
        (function () {
            var token = @json($snapToken);
            var statusUrl = @json(route('payments.status', $order));
            var orderUrl = @json(route('orders.show', $order));
            var button = document.getElementById('pay-button');
            var message = document.getElementById('pay-status');
            var polling = null;

            function setMessage(text) {
                message.textContent = text;
            }

            // Status pembayaran hanya dipercaya dari server (webhook), bukan dari callback popup.
            function startPolling() {
                if (polling) {
                    return;
                }

                polling = setInterval(function () {
                    fetch(statusUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                        .then(function (response) { return response.ok ? response.json() : null; })
                        .then(function (data) {
                            if (!data) {
                                return;
                            }

                            if (data.payment_status === 'paid') {
                                clearInterval(polling);
                                window.location.href = orderUrl;
                            } else if (data.payment_status === 'expired' || data.order_status === 'cancelled') {
                                clearInterval(polling);
                                window.location.reload();
                            }
                        })
                        .catch(function () { /* coba lagi pada putaran berikutnya */ });
                }, 4000);
            }

            function openSnap() {
                snap.pay(token, {
                    onSuccess: function () {
                        setMessage('Pembayaran diterima. Memverifikasi...');
                    },
                    onPending: function () {
                        setMessage('Menunggu pembayaran. Halaman ini diperbarui otomatis setelah pembayaran terverifikasi.');
                    },
                    onError: function () {
                        setMessage('Pembayaran gagal. Silakan coba lagi.');
                    },
                    onClose: function () {
                        // Tidak ada redirect/reload: popup bisa ditutup, tombol di bawah membukanya lagi.
                        setMessage('Popup ditutup. Klik "Bayar Sekarang" untuk melanjutkan.');
                    }
                });
            }

            button.addEventListener('click', openSnap);
            document.addEventListener('DOMContentLoaded', function () {
                startPolling();
                openSnap();
            });
        })();
    </script>
@endif

    </div>
</x-layouts.app>
