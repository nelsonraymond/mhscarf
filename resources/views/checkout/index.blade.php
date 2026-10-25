<x-layouts.app>
    @section('title', 'Checkout Pesanan — MH Scarf')

    {{-- Checkout uses a server-validated RajaOngkir quote token for the final total. --}}

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

        {{-- General + field errors (address_id, shipping quote, cart) --}}
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
            <input type="hidden" name="shipping_quote_token" id="shipping-quote-token" value="">

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

                    <!-- 2. Shipping Quote -->
                    <section class="bg-white rounded-2xl border border-line p-5 sm:p-6 shadow-card" aria-labelledby="co-shipping-h">
                        <div class="flex items-center gap-2.5 pb-4 border-b border-line mb-5">
                            <span class="w-7 h-7 rounded-full bg-pink-deep/15 text-pink-deep flex items-center justify-center text-xs font-bold" aria-hidden="true">2</span>
                            <h2 id="co-shipping-h" class="font-semibold text-base text-ink">Kurir dan Ongkos Kirim</h2>
                        </div>

                        <div class="space-y-4">
                            <p class="text-xs text-muted">Tarif dihitung dari alamat dan berat seluruh produk di keranjang.</p>
                            @if ($shippingWeight)
                                <p class="text-xs text-muted">Berat paket: <strong class="text-ink">{{ number_format($shippingWeight, 0, ',', '.') }} gram</strong></p>
                            @endif
                            <div id="shipping-message" role="status" aria-live="polite"
                                class="rounded-lg bg-cream px-3 py-2 text-xs text-muted {{ $shippingWeightError ? 'text-rose-600' : 'hidden' }}">
                                {{ $shippingWeightError }}
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="shipping-destination" class="block text-xs font-medium text-muted mb-1">Kelurahan / tujuan terverifikasi</label>
                                    <select id="shipping-destination" disabled class="w-full h-11 px-3 rounded-xl border border-line bg-cream/40 text-sm text-ink focus-visible:ring-2 focus-visible:ring-pink-deep">
                                        <option value="">Pilih alamat terlebih dahulu</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="shipping-courier" class="block text-xs font-medium text-muted mb-1">Kurir</label>
                                    <select id="shipping-courier" disabled class="w-full h-11 px-3 rounded-xl border border-line bg-cream/40 text-sm text-ink focus-visible:ring-2 focus-visible:ring-pink-deep">
                                        <option value="" selected>Pilih kurir</option>
                                        @foreach ($shippingCouriers as $courier)
                                            <option value="{{ $courier }}">{{ strtoupper($courier) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <button type="button" id="shipping-calculate" disabled
                                class="inline-flex items-center justify-center h-10 px-4 rounded-xl bg-pink-deep text-white text-sm font-medium disabled:cursor-not-allowed disabled:opacity-50 hover:bg-pink-mauve transition">
                                Hitung ongkir
                            </button>
                            <div id="shipping-results" class="space-y-2" role="radiogroup" aria-label="Pilihan layanan pengiriman"></div>
                            @error('shipping_quote_token')
                                <p class="text-xs text-rose-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
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
                                                <img src="{{ $primaryImage->image_url }}" alt="{{ $product->name }}"
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
                                disabled
                                class="w-full py-3.5 px-4 bg-pink-deep hover:bg-pink-mauve disabled:bg-stone-300 disabled:cursor-not-allowed text-white font-semibold text-sm rounded-xl shadow-card transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-pink-deep">
                                Konfirmasi &amp; Bayar
                            </button>
                            <p id="checkout-guidance" class="text-xs text-muted text-center mt-2" role="status" aria-live="polite">
                                {{ $addresses->isEmpty() ? 'Silakan tambahkan alamat pengiriman terlebih dahulu.' : 'Pilih alamat, kurir, lalu hitung ongkir untuk melanjutkan.' }}
                            </p>
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
<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
    data-client-key="{{ config('midtrans.client_key') }}"></script>
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
            form.dataset.submitting = state ? 'true' : 'false';
            form.dispatchEvent(new Event('checkout:state-change'));
            buttons.forEach(function (btn) {
                btn.disabled = state;
                var spinner = btn.querySelector('.spinner');
                if (spinner) spinner.classList.toggle('hidden', !state);
            });
        }

        function extractMessage(err) {
            if (err && err.status >= 500) {
                return err.order_id
                    ? 'Pesanan sudah dibuat, tetapi pembayaran belum dapat disiapkan. Buka halaman pesanan atau coba lagi dengan aman.'
                    : 'Server mengalami kendala saat checkout. Hasil pesanan belum dapat dipastikan; token checkout mencegah pesanan ganda jika Anda mencoba lagi.';
            }
            if (err && err.status === 401) return 'Sesi Anda telah berakhir. Silakan masuk kembali sebelum checkout.';
            if (err && err.status === 403) return 'Anda tidak memiliki akses untuk melakukan checkout ini.';
            if (err && err.status === 419) return 'Sesi checkout kedaluwarsa. Muat ulang halaman, hitung ongkir kembali, lalu coba lagi.';
            if (err && err.status === 422 && err.errors) {
                var validation = Object.values(err.errors)[0];
                return Array.isArray(validation) ? validation[0] : validation;
            }
            if (err && err.error) return err.error;
            if (err && err.errors) {
                var first = Object.values(err.errors)[0];
                return Array.isArray(first) ? first[0] : first;
            }
            if (err && (err.name === 'AbortError' || err.networkError)) return 'Respons checkout belum diterima. Pesanan mungkin sudah tercatat; token checkout mencegah pesanan ganda. Anda dapat mencoba lagi dengan aman.';
            return 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.';
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (submitting) return;
            if (!form.reportValidity()) return;
            if (form.dataset.shippingBusy === 'true' || !form.querySelector('input[name="address_id"]:checked')
                || !document.getElementById('shipping-courier').value
                || !document.getElementById('shipping-quote-token').value) {
                alert('Pilih alamat, kurir, dan layanan ongkir yang valid sebelum checkout.');
                return;
            }
            setLoading(true);
            var controller = new AbortController();
            var timeout = window.setTimeout(function () { controller.abort(); }, 90000);

            try {
                var res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: new FormData(form),
                    credentials: 'same-origin',
                    signal: controller.signal
                });

                var data = await res.json().catch(function () { return {}; });
                if (res.redirected && /\/login(?:\?|$)/.test(res.url)) {
                    throw { status: 401 };
                }
                if (!res.ok) {
                    data.status = res.status;
                    throw data;
                }
                if (!data.order_id) {
                    throw { networkError: true };
                }

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
            } finally {
                window.clearTimeout(timeout);
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
                    disabled
                    class="shrink-0 inline-flex items-center justify-center px-5 h-12 rounded-xl bg-pink-deep text-white font-medium text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-pink-deep active:bg-pink-mauve disabled:bg-stone-300 disabled:cursor-not-allowed transition">
                    <svg class="spinner hidden mr-2 h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    Buat Pesanan
                </button>
            </div>
        </div>
    </div>

    <script type="module">
    import { syncCheckoutSubmitButtons } from @json(asset('js/checkout-state.js'));
    document.addEventListener('DOMContentLoaded', function () {
        var subtotal = {{ (int) $subtotal }};
        var state = { cost: 0, discount: 0, code: '', quoteToken: '', quoteExpiresAt: 0 };
        var form = document.getElementById('checkout-form');
        var addressRadios = document.querySelectorAll('input[name="address_id"]');
        var destination = document.getElementById('shipping-destination');
        var courier = document.getElementById('shipping-courier');
        var calculateButton = document.getElementById('shipping-calculate');
        var results = document.getElementById('shipping-results');
        var shippingMessage = document.getElementById('shipping-message');
        var quoteField = document.getElementById('shipping-quote-token');
        var checkoutGuidance = document.getElementById('checkout-guidance');
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
        var destinationEndpoint = @json(route('checkout.shipping.destinations'));
        var quoteEndpoint = @json(route('checkout.shipping.quotes'));
        var voucherEndpoint = @json(route('checkout.voucher'));
        var quoteLoading = false;
        var destinationsLoading = false;
        var shippingRequestId = 0;

        function rupiah(value) { return 'Rp' + Number(value).toLocaleString('id-ID'); }
        function selectedAddressId() {
            var selected = document.querySelector('input[name="address_id"]:checked');
            return selected ? selected.value : '';
        }
        function setShippingMessage(text, error) {
            shippingMessage.textContent = text || '';
            shippingMessage.classList.toggle('hidden', !text);
            shippingMessage.classList.toggle('text-rose-600', !!error);
            shippingMessage.classList.toggle('text-emerald-700', !!text && !error);
        }
        function renderTotal() {
            if (elShipping) elShipping.textContent = state.quoteToken ? rupiah(state.cost) : 'Pilih layanan';
            var total = subtotal + state.cost - state.discount;
            if (elTotal) elTotal.textContent = rupiah(total);
            if (elBar) elBar.textContent = rupiah(total);
            if (elDiscountRow) {
                var show = state.discount > 0;
                elDiscountRow.classList.toggle('hidden', !show);
                elDiscountRow.classList.toggle('flex', show);
                if (elDiscount) elDiscount.textContent = '- ' + rupiah(state.discount);
            }
            var addressReady = !!selectedAddressId();
            var courierReady = !!courier.value;
            var quoteReady = !!state.quoteToken && Number.isInteger(state.cost) && state.cost > 0
                && Number.isInteger(state.quoteExpiresAt) && state.quoteExpiresAt * 1000 > Date.now();
            var busy = quoteLoading || destinationsLoading || form.dataset.submitting === 'true';
            form.dataset.shippingBusy = quoteLoading || destinationsLoading ? 'true' : 'false';
            syncCheckoutSubmitButtons(document, {
                addressReady: addressReady,
                courierReady: courierReady,
                quoteToken: state.quoteToken,
                cost: state.cost,
                quoteExpiresAt: state.quoteExpiresAt,
                busy: busy
            });
            if (checkoutGuidance) {
                checkoutGuidance.textContent = busy ? 'Tunggu proses pengiriman atau checkout selesai.'
                    : !addressReady ? 'Pilih alamat pengiriman.'
                    : !courierReady ? 'Pilih kurir dan hitung ongkir.'
                    : !quoteReady ? 'Pilih tujuan dan hitung ongkir.' : 'Alamat dan ongkir sudah siap.';
            }
        }
        function clearQuote() {
            shippingRequestId++;
            quoteLoading = false;
            state.cost = 0;
            state.quoteToken = '';
            state.quoteExpiresAt = 0;
            quoteField.value = '';
            results.replaceChildren();
            clearVoucher();
            renderTotal();
        }
        function firstError(data, fallback) {
            if (data && data.errors) {
                var first = Object.values(data.errors)[0];
                return Array.isArray(first) ? first[0] : first;
            }
            return (data && data.message) || fallback;
        }
        async function postJson(url, payload) {
            var response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload),
                credentials: 'same-origin'
            });
            var data = await response.json().catch(function () { return {}; });
            if (!response.ok) throw data;
            return data;
        }
        function clearVoucher() {
            state.code = '';
            state.discount = 0;
            field.value = '';
            formRow.classList.remove('hidden');
            applied.classList.add('hidden');
            applied.classList.remove('flex');
            renderTotal();
        }
        async function loadDestinations() {
            clearQuote();
            clearVoucher();
            var addressId = selectedAddressId();
            destinationsLoading = !!addressId;
            courier.value = '';
            destination.disabled = true;
            courier.disabled = !addressId || {{ $shippingWeight ? 'false' : 'true' }};
            calculateButton.disabled = true;
            destination.replaceChildren(new Option(addressId ? 'Memuat tujuan…' : 'Pilih alamat terlebih dahulu', ''));
            renderTotal();
            if (!addressId) { destinationsLoading = false; renderTotal(); return; }
            var destinationRequestId = shippingRequestId;

            try {
                var data = await postJson(destinationEndpoint, { address_id: addressId });
                if (destinationRequestId !== shippingRequestId || addressId !== selectedAddressId()) return;
                destination.replaceChildren();
                if (!data.destinations || data.destinations.length === 0) {
                    destination.add(new Option('Tujuan tidak cocok dengan alamat', ''));
                    setShippingMessage('Alamat belum cocok dengan data RajaOngkir. Lengkapi kelurahan/desa, kecamatan, kota, provinsi, dan kode pos.', true);
                    return;
                }
                destination.add(new Option('Pilih kelurahan / tujuan', ''));
                data.destinations.forEach(function (item) {
                    destination.add(new Option(item.label, item.id));
                });
                destination.disabled = false;
                calculateButton.disabled = !{{ $shippingWeight ? 'false' : 'true' }} || !courier.value;
                setShippingMessage(data.destinations.length + ' tujuan cocok dengan alamat yang dipilih.', false);
                if (data.destinations.length === 1) {
                    destination.value = String(data.destinations[0].id);
                    calculateButton.click();
                }
            } catch (error) {
                if (destinationRequestId !== shippingRequestId || addressId !== selectedAddressId()) return;
                destination.replaceChildren(new Option('Pencarian tujuan gagal', ''));
                setShippingMessage(firstError(error, 'Tujuan tidak dapat dicari. Silakan coba lagi.'), true);
            } finally {
                if (destinationRequestId === shippingRequestId && addressId === selectedAddressId()) {
                    destinationsLoading = false;
                    renderTotal();
                }
            }
        }
        function renderQuotes(quotes) {
            results.replaceChildren();
            quotes.forEach(function (quote, index) {
                var label = document.createElement('label');
                label.className = 'block p-4 rounded-xl border border-line bg-white cursor-pointer hover:border-pink-deep/70 transition';
                var row = document.createElement('div');
                row.className = 'flex items-start justify-between gap-3';
                var info = document.createElement('div');
                info.className = 'flex items-start gap-3 min-w-0';
                var radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = 'shipping-service-option';
                radio.value = quote.service;
                radio.className = 'mt-1 w-4 h-4 accent-pink-deep shrink-0';
                radio.addEventListener('change', function () {
                    var cost = Number(quote.cost);
                    if (!quote.quote_token || !Number.isInteger(cost) || cost <= 0
                        || !Number.isInteger(Number(quote.expires_at)) || Number(quote.expires_at) * 1000 <= Date.now()) {
                        clearQuote();
                        setShippingMessage('Tarif ongkir tidak valid. Silakan hitung ulang.', true);
                        return;
                    }
                    state.quoteToken = quote.quote_token;
                    state.cost = cost;
                    state.quoteExpiresAt = Number(quote.expires_at);
                    quoteField.value = quote.quote_token;
                    renderTotal();
                    window.setTimeout(function () {
                        if (state.quoteToken === quote.quote_token && Date.now() >= state.quoteExpiresAt * 1000) {
                            clearQuote();
                            setShippingMessage('Ongkir kedaluwarsa. Silakan hitung ulang.', true);
                        }
                    }, Math.max(0, state.quoteExpiresAt * 1000 - Date.now()));
                    if (state.code) applyVoucher(state.code);
                    results.querySelectorAll('label').forEach(function (item) {
                        item.classList.toggle('border-pink-deep', item.contains(document.querySelector('input[name="shipping-service-option"]:checked')));
                        item.classList.toggle('bg-cream/60', item.contains(document.querySelector('input[name="shipping-service-option"]:checked')));
                    });
                });
                var text = document.createElement('div');
                text.className = 'min-w-0';
                var title = document.createElement('p');
                title.className = 'font-semibold text-sm text-ink';
                title.textContent = quote.courier_name + ' — ' + quote.service;
                var description = document.createElement('p');
                description.className = 'text-xs text-muted mt-0.5';
                description.textContent = quote.description || '';
                var etd = document.createElement('p');
                etd.className = 'text-xs text-muted mt-1';
                etd.textContent = quote.etd ? 'Estimasi: ' + quote.etd : '';
                text.append(title, description, etd);
                var cost = document.createElement('span');
                cost.className = 'font-semibold text-sm text-ink shrink-0';
                cost.textContent = rupiah(quote.cost);
                info.append(radio, text);
                row.append(info, cost);
                label.append(row);
                results.append(label);
                if (index === 0) radio.checked = false;
            });
            if (quotes.length === 0) setShippingMessage('Tidak ada layanan untuk rute ini. Coba kurir lain.', true);
        }
        async function calculateQuotes() {
            var addressId = selectedAddressId();
            if (!addressId || !destination.value) {
                setShippingMessage('Pilih alamat dan tujuan terlebih dahulu.', true);
                return;
            }
            calculateButton.disabled = true;
            calculateButton.textContent = 'Menghitung…';
            clearQuote();
            quoteLoading = true;
            var requestId = shippingRequestId;
            var requestedAddressId = addressId;
            var requestedDestination = destination.value;
            var requestedCourier = courier.value;
            renderTotal();
            setShippingMessage('Meminta tarif terbaru dari kurir…', false);
            try {
                var data = await postJson(quoteEndpoint, {
                    address_id: addressId,
                    destination_id: destination.value,
                    courier: courier.value
                });
                if (requestId !== shippingRequestId || requestedAddressId !== selectedAddressId()
                    || requestedDestination !== destination.value || requestedCourier !== courier.value) return;
                var validQuotes = (data.quotes || []).filter(function (quote) {
                    return quote.quote_token && Number.isInteger(Number(quote.cost)) && Number(quote.cost) > 0
                        && Number.isInteger(Number(quote.expires_at)) && Number(quote.expires_at) * 1000 > Date.now();
                });
                renderQuotes(validQuotes);
                setShippingMessage(validQuotes.length
                    ? 'Berat paket: ' + Number(data.weight_gram).toLocaleString('id-ID') + ' gram.'
                    : 'Tidak ada tarif ongkir valid untuk rute ini. Coba kurir lain.', validQuotes.length === 0);
            } catch (error) {
                if (requestId !== shippingRequestId) return;
                setShippingMessage(firstError(error, 'Tarif ongkir tidak tersedia. Silakan coba lagi.'), true);
            } finally {
                if (requestId === shippingRequestId) {
                    quoteLoading = false;
                    calculateButton.disabled = destinationsLoading || !destination.value || !courier.value;
                    calculateButton.textContent = 'Hitung ongkir';
                    renderTotal();
                }
            }
        }
        async function applyVoucher(code) {
            if (!state.quoteToken) {
                setShippingMessage('Pilih layanan ongkir sebelum memakai voucher.', true);
                return;
            }
            applyBtn.disabled = true;
            try {
                var data = await postJson(voucherEndpoint, {
                    voucher_code: code,
                    address_id: selectedAddressId(),
                    shipping_quote_token: state.quoteToken
                });
                state.code = data.code;
                state.discount = Number(data.discount) || 0;
                field.value = data.code;
                appliedCode.textContent = data.code;
                formRow.classList.add('hidden');
                applied.classList.remove('hidden');
                applied.classList.add('flex');
                input.value = '';
                setShippingMessage('Hemat ' + rupiah(state.discount) + ' dengan voucher ini.', false);
                renderTotal();
            } catch (error) {
                clearVoucher();
                setShippingMessage(firstError(error, 'Voucher tidak dapat digunakan.'), true);
            } finally {
                applyBtn.disabled = false;
            }
        }

        addressRadios.forEach(function (radio) {
            radio.addEventListener('change', loadDestinations);
        });
        destination.addEventListener('change', function () {
            clearQuote();
            calculateButton.disabled = !destination.value || !courier.value || destinationsLoading;
        });
        courier.addEventListener('change', function () {
            clearQuote();
            calculateButton.disabled = !destination.value || !courier.value || destinationsLoading;
        });
        calculateButton.addEventListener('click', calculateQuotes);
        applyBtn.addEventListener('click', function () {
            var code = input.value.trim();
            if (!code) { setShippingMessage('Masukkan kode voucher terlebih dahulu.', true); return; }
            applyVoucher(code);
        });
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') { event.preventDefault(); applyBtn.click(); }
        });
        removeBtn.addEventListener('click', function () {
            clearVoucher();
            setShippingMessage('', false);
        });
        form.addEventListener('checkout:state-change', renderTotal);

        renderTotal();
        if (selectedAddressId()) loadDestinations();
    });
    </script>
</x-layouts.app>
