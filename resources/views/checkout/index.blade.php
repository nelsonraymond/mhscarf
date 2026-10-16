<x-layouts.app>
    @section('title', 'Checkout Pesanan — MH Scarf Store')

    {{-- Tahap 4: migrated to shared app layout; per-field errors surfaced; shipping card gets
         selected state + keyboard focus ring; fake "Diskon Voucher - Rp 0" row removed (checkout
         backend has no voucher input); payment info honest (post-order simulated gateway).
         Contract unchanged: POST checkout.store with address_id, shipping_method, customer_note. --}}

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10 pb-28 lg:pb-12">

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="text-xs text-muted mb-6">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded transition">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('cart.index') }}" class="hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded transition">Keranjang</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-ink font-medium">Checkout</li>
            </ol>
        </nav>

        <div class="mb-8">
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink">Checkout Pesanan</h1>
            <p class="text-sm text-muted mt-1.5">Periksa alamat pengiriman, metode kurir, dan rincian hijab pilihan Anda.</p>
        </div>

        {{-- General + field errors (address_id, shipping_method, cart) --}}
        @if ($errors->any())
            <div role="alert" aria-live="assertive" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1">
                <p class="font-semibold text-rose-900">Periksa kesalahan sebelum melanjutkan:</p>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}">
            @csrf
            <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
            <input type="hidden" name="voucher_code" id="voucher-code-field" value="">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

                <!-- ============ LEFT: STEPS & ITEMS ============ -->
                <div class="lg:col-span-8 space-y-6">

                    <!-- 1. Shipping Address -->
                    <section class="bg-white rounded-2xl border border-line p-5 sm:p-6 shadow-card" aria-labelledby="co-address-h">
                        <div class="flex items-center justify-between pb-4 border-b border-line mb-5 gap-3 flex-wrap">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-full bg-pink-deep/15 text-pink-deep flex items-center justify-center text-xs font-bold" aria-hidden="true">1</span>
                                <h2 id="co-address-h" class="font-semibold text-base text-ink">Alamat Pengiriman</h2>
                            </div>
                            <a href="{{ route('addresses.create', ['redirect_to' => route('checkout.index')]) }}"
                                class="inline-flex items-center h-9 px-3 text-xs text-pink-deep hover:text-pink-mauve font-medium rounded-lg transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
                                + Tambah Alamat Baru
                            </a>
                        </div>

                        @if ($addresses->isEmpty())
                            <div class="p-6 rounded-xl bg-cream border border-dashed border-pink-deep/50 text-center">
                                <p class="text-sm font-medium text-ink mb-1">Anda belum memiliki alamat pengiriman tersimpan</p>
                                <p class="text-sm text-muted mb-4">Mohon tambahkan alamat pengiriman terlebih dahulu untuk melanjutkan proses pesanan.</p>
                                <a href="{{ route('addresses.create', ['redirect_to' => route('checkout.index')]) }}"
                                    class="inline-flex items-center justify-center px-4 h-11 bg-pink-deep hover:bg-pink-mauve text-white text-sm font-medium rounded-xl transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-pink-deep">
                                    + Tambah Alamat Sekarang
                                </a>
                            </div>
                        @else
                            <fieldset>
                                <legend class="sr-only">Pilih alamat pengiriman</legend>
                                <div class="space-y-3">
                                    @foreach ($addresses as $addr)
                                        @php $addrChecked = old('address_id', $defaultAddress?->id) == $addr->id; @endphp
                                        <label class="block p-4 rounded-xl border cursor-pointer transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-pink-deep {{ $addrChecked ? 'border-pink-deep bg-cream/60 ring-1 ring-pink-deep/30' : 'border-line bg-white hover:border-pink-deep/70' }}">
                                            <div class="flex items-start gap-3">
                                                <input type="radio" name="address_id" value="{{ $addr->id }}"
                                                    {{ $addrChecked ? 'checked' : '' }}
                                                    class="mt-1 w-4 h-4 accent-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep shrink-0">
                                                <div class="flex-1 min-w-0 text-sm">
                                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mb-1">
                                                        <span class="font-semibold text-ink">{{ $addr->recipient_name }}</span>
                                                        <span class="text-muted text-xs">{{ $addr->phone }}</span>
                                                        @if ($addr->label)
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-stone-100 text-muted">{{ $addr->label }}</span>
                                                        @endif
                                                        @if ($addr->is_default)
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-pink-deep/15 text-pink-deep">Utama</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-ink leading-relaxed text-sm">{{ $addr->fullAddress() }}</p>
                                                    @if ($addr->notes)
                                                        <p class="text-xs text-muted italic mt-1">Patokan: {{ $addr->notes }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('address_id')
                                    <p class="text-xs text-rose-600 mt-2" role="alert">{{ $message }}</p>
                                @enderror
                            </fieldset>
                        @endif
                    </section>

                    <!-- 2. Shipping Method -->
                    <section class="bg-white rounded-2xl border border-line p-5 sm:p-6 shadow-card" aria-labelledby="co-shipping-h">
                        <div class="flex items-center gap-2.5 pb-4 border-b border-line mb-5">
                            <span class="w-7 h-7 rounded-full bg-pink-deep/15 text-pink-deep flex items-center justify-center text-xs font-bold" aria-hidden="true">2</span>
                            <h2 id="co-shipping-h" class="font-semibold text-base text-ink">Metode Pengiriman</h2>
                        </div>

                        <fieldset>
                            <legend class="sr-only">Pilih metode pengiriman</legend>
                            <div class="space-y-3">
                                @foreach ($shippingMethods as $method)
                                    @php $shipChecked = old('shipping_method', $defaultShipping) === $method['code']; @endphp
                                    <label class="block p-4 rounded-xl border cursor-pointer transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-pink-deep {{ $shipChecked ? 'border-pink-deep bg-cream/60 ring-1 ring-pink-deep/30' : 'border-line bg-white hover:border-pink-deep/70' }}">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-start gap-3 min-w-0">
                                                <input type="radio" name="shipping_method" value="{{ $method['code'] }}"
                                                    data-cost="{{ $method['cost'] }}"
                                                    {{ $shipChecked ? 'checked' : '' }}
                                                    class="shipping-method-radio mt-1 w-4 h-4 accent-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep shrink-0">
                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                        <span class="font-semibold text-sm text-ink">{{ $method['courier'] }} — {{ $method['name'] }}</span>
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-cream border border-line text-muted">
                                                            {{ $method['estimated_days'] }}
                                                        </span>
                                                    </div>
                                                    <p class="text-xs text-muted mt-0.5">{{ $method['description'] }}</p>
                                                </div>
                                            </div>
                                            <span class="font-semibold text-sm text-ink shrink-0">
                                                Rp{{ number_format($method['cost'], 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('shipping_method')
                                <p class="text-xs text-rose-600 mt-2" role="alert">{{ $message }}</p>
                            @enderror
                        </fieldset>
                    </section>

                    <!-- 3. Customer Note -->
                    <section class="bg-white rounded-2xl border border-line p-5 sm:p-6 shadow-card" aria-labelledby="co-note-h">
                        <div class="flex items-center gap-2.5 pb-4 border-b border-line mb-4">
                            <span class="w-7 h-7 rounded-full bg-pink-deep/15 text-pink-deep flex items-center justify-center text-xs font-bold" aria-hidden="true">3</span>
                            <h2 id="co-note-h" class="font-semibold text-base text-ink">Catatan Pesanan <span class="font-normal text-muted text-sm">(Opsional)</span></h2>
                        </div>

                        <label for="customer_note" class="sr-only">Catatan untuk pesanan</label>
                        <textarea name="customer_note" id="customer_note" rows="3" maxlength="500"
                            placeholder="Tambahkan instruksi khusus untuk pesanan atau kurir (maks. 500 karakter)..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-cream/40 text-sm text-ink placeholder:text-muted/70 focus:outline-none focus:border-pink-deep focus-visible:ring-2 focus-visible:ring-pink-deep transition {{ $errors->has('customer_note') ? 'border-rose-300' : '' }}">{{ old('customer_note') }}</textarea>
                        @error('customer_note')
                            <p class="text-xs text-rose-600 mt-1.5" role="alert">{{ $message }}</p>
                        @enderror
                    </section>

                    <!-- 4. Order Items Review -->
                    <section class="bg-white rounded-2xl border border-line p-5 sm:p-6 shadow-card" aria-labelledby="co-items-h">
                        <div class="flex items-center justify-between pb-4 border-b border-line mb-4 gap-3 flex-wrap">
                            <h2 id="co-items-h" class="font-semibold text-base text-ink">Produk yang Dipesan ({{ $itemCount }} item)</h2>
                            <a href="{{ route('cart.index') }}" class="inline-flex items-center h-9 px-3 text-xs text-pink-deep hover:text-pink-mauve font-medium rounded-lg transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">Ubah Keranjang</a>
                        </div>

                        <ul class="divide-y divide-line list-none m-0 p-0">
                            @foreach ($cartItems as $item)
                                @php
                                    $variant = $item->variant;
                                    $product = $variant->product;
                                    $primaryImage = $product->primaryImage();
                                @endphp
                                <li class="py-4 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-14 h-14 rounded-xl bg-cream border border-line overflow-hidden shrink-0 flex items-center justify-center">
                                            @if ($primaryImage)
                                                <img src="{{ Storage::url($primaryImage->image_path) }}" alt="{{ $product->name }}"
                                                    loading="lazy" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-[10px] tracking-widest text-pink-deep uppercase" aria-hidden="true">MH Scarf</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <h3 class="font-medium text-sm text-ink line-clamp-2">{{ $product->name }}</h3>
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-muted mt-0.5">
                                                <span>Varian: <strong class="text-ink font-medium">{{ $variant->name }}</strong></span>
                                                <span aria-hidden="true">•</span>
                                                <span class="text-[11px]">{{ $variant->sku }}</span>
                                            </div>
                                            <p class="text-xs text-muted mt-0.5">
                                                {{ $item->quantity }} × Rp{{ number_format($variant->finalPrice(), 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="font-semibold text-sm text-ink">
                                            Rp{{ number_format($variant->finalPrice() * $item->quantity, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                </div>

                <!-- ============ RIGHT: SUMMARY ============ -->
                <aside class="lg:col-span-4" aria-label="Ringkasan pembayaran">
                    <div class="bg-white rounded-2xl border border-line p-6 shadow-card space-y-5 lg:sticky lg:top-24">
                        <h2 class="font-display text-lg font-bold text-ink pb-3 border-b border-line">Ringkasan Pembayaran</h2>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-muted">
                                <span>Subtotal Produk</span>
                                <span class="font-medium text-ink">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between text-muted">
                                <span>Biaya Pengiriman</span>
                                <span id="summary-shipping" class="font-medium text-ink">Rp{{ number_format($shippingCost, 0, ',', '.') }}</span>
                                <div id="summary-discount-row" class="hidden justify-between text-emerald-700">
    <span>Diskon Voucher</span>
    <span id="summary-discount" class="font-medium">- Rp0</span>
</div>
                            </div>

                            <div class="pt-3 border-t border-line flex justify-between items-baseline">
                                <span class="font-semibold text-base text-ink">Total Pembayaran</span>
                                <div class="text-right">
                                    <span id="summary-grand-total" class="font-display text-xl font-bold text-pink-deep">
                                        Rp{{ number_format($grandTotal, 0, ',', '.') }}
                                    </span>
                                    <p id="summary-note" class="text-[11px] text-muted mt-0.5">Sesuai perhitungan server.</p>
                                </div>
                            </div>
                        </div>

                        <div>
    <label for="voucher-input" class="block text-xs font-semibold uppercase tracking-wide text-muted">Kode Voucher</label>

    <div id="voucher-form-row" class="flex gap-2 mt-1.5">
        <input id="voucher-input" type="text" maxlength="50" autocomplete="off" placeholder="Masukkan kode"
            class="flex-1 min-w-0 h-11 px-3.5 rounded-xl border border-line bg-cream/40 text-sm text-ink uppercase placeholder:normal-case placeholder:text-muted/70 focus:outline-none focus:border-pink-deep focus-visible:ring-2 focus-visible:ring-pink-deep transition">
        <button type="button" id="voucher-apply"
            class="shrink-0 px-4 h-11 rounded-xl border border-pink-deep text-pink-deep hover:bg-pink-soft/30 text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
            Pakai
        </button>
    </div>

    <div id="voucher-applied" class="hidden mt-1.5 items-center justify-between gap-3 px-3.5 h-11 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-800">
        <span>Voucher <strong id="voucher-applied-code"></strong> terpasang</span>
        <button type="button" id="voucher-remove" class="text-xs font-medium text-emerald-700 hover:text-emerald-900 underline">Hapus</button>
    </div>

    <p id="voucher-msg" class="hidden text-xs mt-1.5" role="status" aria-live="polite"></p>
</div>

                        <!-- Confirmation Button -->
                        <div class="pt-1">
                            <button type="submit"
                                {{ $addresses->isEmpty() ? 'disabled' : '' }}
                                class="w-full py-3.5 px-4 bg-pink-deep hover:bg-pink-mauve disabled:bg-stone-300 disabled:cursor-not-allowed text-white font-semibold text-sm rounded-xl shadow-card transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-pink-deep">
                                Konfirmasi &amp; Bayar
                            </button>
                            @if ($addresses->isEmpty())
                                <p class="text-xs text-rose-600 text-center mt-2" role="note">
                                    Tambahkan alamat pengiriman terlebih dahulu untuk konfirmasi pesanan.
                                </p>
                            @endif
                        </div>

                        <!-- Payment info: honest — no payment option is collected at checkout;
                             payment is completed afterwards via the order's payment page. -->
                        <div class="pt-4 border-t border-line">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-muted mb-2">Informasi Pembayaran</h3>
                            <p class="text-xs text-muted leading-relaxed">
                                Setelah pesanan dibuat, Anda dapat melanjutkan ke halaman pembayaran
                                untuk menyelesaikan transaksi pesanan ini.
                            </p>
                        </div>

                        <!-- Trust Features -->
                        <div class="pt-4 border-t border-line space-y-2 text-xs text-muted">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span>Stok produk dikunci otomatis saat pemesanan</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span>Rincian produk &amp; harga tersimpan permanen (snapshot)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span>Perhitungan harga &amp; stok diverifikasi server-side</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </form>

    {{-- Midtrans Snap JS (Sandbox) --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('checkout-form');
        if (!form) return;

        var paymentUrlTemplate = @json(route('payments.show', ['order' => '__ID__']));
        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var buttons = document.querySelectorAll('#checkout-form button[type="submit"], button[form="checkout-form"]');
        var submitting = false;

        function setLoading(state) {
            submitting = state;
            buttons.forEach(function (btn) {
                btn.disabled = state;
                var spinner = btn.querySelector('.spinner');
                if (spinner) spinner.classList.toggle('hidden', !state);
            });
        }

        function extractMessage(err) {
            if (err && err.error) return err.error;
            if (err && err.errors) {
                var first = Object.values(err.errors)[0];
                return Array.isArray(first) ? first[0] : first;
            }
            return (err && err.message) || 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.';
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (submitting) return;
            setLoading(true);

            try {
                var res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: new FormData(form),
                    credentials: 'same-origin'
                });

                var data = await res.json().catch(function () { return {}; });
                if (!res.ok) throw data;

                // Pesanan sudah dibuat di server; semua jalur akhir diarahkan ke halaman pembayaran
                // (yang bisa melanjutkan pembayaran / sinkron status), BUKAN kembali ke checkout.
                var orderUrl = paymentUrlTemplate.replace('__ID__', data.order_id);

                // If Midtrans provides a direct redirect URL, go there immediately
                if (data.redirect_url) {
                    window.location.assign(data.redirect_url);
                    return;
                }
                // Fallback: if no snap token or Snap JS not loaded, go to order details page
                if (!data.snap_token || !window.snap) {
                    window.location.href = orderUrl;
                    return;
                }
                // If Snap JS is available, use it as a fallback (should rarely happen)
                window.snap.pay(data.snap_token, {
                    onSuccess: function () { window.location.href = orderUrl; },
                    onPending: function () { window.location.href = orderUrl; },
                    onError:   function () { window.location.href = orderUrl; },
                    onClose:   function () { window.location.href = orderUrl; }
                });
            } catch (err) {
                setLoading(false);
                alert(extractMessage(err));
            }
        });
    });
</script>

        {{-- Sticky summary bar (mobile/tablet): total always reachable --}}
        <div class="fixed bottom-0 inset-x-0 z-50 lg:hidden bg-white border-t border-line shadow-[0_-4px_16px_rgba(58,48,51,0.08)]">
            <div class="px-4 py-3 flex items-center justify-between gap-4 max-w-7xl mx-auto">
                <div>
                    <p class="text-[11px] text-muted leading-tight">Total Pembayaran</p>
                    <p id="bar-grand-total" class="text-base font-bold text-ink leading-tight font-display">Rp{{ number_format($grandTotal, 0, ',', '.') }}</p>
                </div>
                <button id="checkout-submit" type="submit" form="checkout-form"
                    {{ $addresses->isEmpty() ? 'disabled' : '' }}
                    class="shrink-0 inline-flex items-center justify-center px-5 h-12 rounded-xl bg-pink-deep text-white font-medium text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-pink-deep active:bg-pink-mauve disabled:bg-stone-300 disabled:cursor-not-allowed transition">
                    <svg class="spinner hidden mr-2 h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    Buat Pesanan
                </button>
            </div>
        </div>
    </div>

    <script>
    // Pratinjau saja. Server menghitung ulang ongkir dan diskon saat pesanan dibuat.
    document.addEventListener('DOMContentLoaded', function () {
        var subtotal = {{ (int) $subtotal }};
        var state = { cost: {{ (int) $shippingCost }}, discount: 0, code: '' };

        var radios = document.querySelectorAll('.shipping-method-radio');
        var elShipping = document.getElementById('summary-shipping');
        var elTotal = document.getElementById('summary-grand-total');
        var elBar = document.getElementById('bar-grand-total');
        var elDiscountRow = document.getElementById('summary-discount-row');
        var elDiscount = document.getElementById('summary-discount');

        var field = document.getElementById('voucher-code-field');
        var input = document.getElementById('voucher-input');
        var applyBtn = document.getElementById('voucher-apply');
        var removeBtn = document.getElementById('voucher-remove');
        var formRow = document.getElementById('voucher-form-row');
        var applied = document.getElementById('voucher-applied');
        var appliedCode = document.getElementById('voucher-applied-code');
        var msg = document.getElementById('voucher-msg');

        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var endpoint = @json(route('checkout.voucher'));

        function rupiah(n) { return 'Rp' + Number(n).toLocaleString('id-ID'); }

        function render() {
            if (elShipping) elShipping.textContent = rupiah(state.cost);
            var total = subtotal + state.cost - state.discount;
            if (elTotal) elTotal.textContent = rupiah(total);
            if (elBar) elBar.textContent = rupiah(total);

            if (elDiscountRow) {
                var show = state.discount > 0;
                elDiscountRow.classList.toggle('hidden', !show);
                elDiscountRow.classList.toggle('flex', show);
                if (elDiscount) elDiscount.textContent = '- ' + rupiah(state.discount);
            }
        }

        function setMessage(text, ok) {
            if (!msg) return;
            msg.textContent = text || '';
            msg.classList.toggle('hidden', !text);
            msg.classList.toggle('text-emerald-700', !!ok);
            msg.classList.toggle('text-rose-600', !ok);
        }

        function clearVoucher() {
            state.code = '';
            state.discount = 0;
            field.value = '';
            formRow.classList.remove('hidden');
            applied.classList.add('hidden');
            applied.classList.remove('flex');
            render();
        }

        function firstError(data) {
            if (data && data.errors) {
                var first = Object.values(data.errors)[0];
                return Array.isArray(first) ? first[0] : first;
            }
            return (data && data.message) || 'Voucher tidak dapat digunakan.';
        }

        async function applyVoucher(code) {
            var radio = document.querySelector('.shipping-method-radio:checked');
            applyBtn.disabled = true;

            try {
                var res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ voucher_code: code, shipping_method: radio ? radio.value : 'regular' }),
                    credentials: 'same-origin'
                });
                var data = await res.json().catch(function () { return {}; });
                if (!res.ok) throw data;

                state.code = data.code;
                state.discount = Number(data.discount) || 0;
                field.value = data.code;
                appliedCode.textContent = data.code;
                formRow.classList.add('hidden');
                applied.classList.remove('hidden');
                applied.classList.add('flex');
                input.value = '';
                setMessage('Hemat ' + rupiah(state.discount) + ' dengan voucher ini.', true);
                render();
            } catch (err) {
                clearVoucher();
                setMessage(firstError(err), false);
            } finally {
                applyBtn.disabled = false;
            }
        }

        applyBtn.addEventListener('click', function () {
            var code = input.value.trim();
            if (!code) { setMessage('Masukkan kode voucher terlebih dahulu.', false); return; }
            applyVoucher(code);
        });

        // Enter di kolom voucher tidak boleh mengirim form checkout
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); applyBtn.click(); }
        });

        removeBtn.addEventListener('click', function () {
            clearVoucher();
            setMessage('', true);
        });

        radios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (!this.checked) return;
                state.cost = parseFloat(this.dataset.cost || 0);

                // Voucher gratis ongkir bergantung pada metode pengiriman, jadi hitung ulang
                if (state.code) { applyVoucher(state.code); } else { render(); }

                document.querySelectorAll('label:has(.shipping-method-radio)').forEach(function (label) {
                    var r = label.querySelector('.shipping-method-radio');
                    if (!r) return;
                    label.classList.toggle('border-pink-deep', r.checked);
                    label.classList.toggle('bg-cream/60', r.checked);
                    label.classList.toggle('ring-1', r.checked);
                    label.classList.toggle('ring-pink-deep/30', r.checked);
                    label.classList.toggle('border-line', !r.checked);
                    label.classList.toggle('bg-white', !r.checked);
                });
            });
        });
    });
</script>
</x-layouts.app>
