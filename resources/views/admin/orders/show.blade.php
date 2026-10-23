<x-layouts.admin>
    <x-slot:title>Pesanan #{{ $order->order_number }} - MH Scarf Admin</x-slot:title>
    <x-slot:header>Detail Pesanan</x-slot:header>

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-admin-muted hover:text-admin-text">← Kembali ke daftar pesanan</a>
    </div>

    @php
    $refundPayments = $order->payments->filter(fn ($p) => ($p->metadata['needs_refund'] ?? false) === true);
@endphp
@if ($refundPayments->isNotEmpty())
    <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-5" role="alert">
        <h2 class="text-sm font-semibold text-amber-900">Pesanan ini perlu direfund</h2>
        <ul class="mt-2 space-y-1 text-xs text-amber-800">
            @foreach ($refundPayments as $rp)
                <li>
                    {{ $rp->metadata['needs_refund_reason'] ?? 'Pembayaran perlu dikembalikan.' }}
                    Nominal: <strong>{{ $rp->formattedAmount() }}</strong>
                    · Ref: <span class="font-mono">{{ $rp->transaction_reference ?? '—' }}</span>
                </li>
            @endforeach
        </ul>
        <p class="mt-2 text-xs text-amber-800">Lakukan refund di dashboard Midtrans terlebih dahulu, baru tandai di sini.</p>

        <form method="POST" action="{{ route('admin.orders.refunded', $order) }}" class="mt-3"
            data-confirm="Pastikan dana sudah benar-benar dikembalikan ke pelanggan. Tindakan ini mencatat refund sebagai selesai."
            data-confirm-title="Tandai sudah direfund?"
            data-confirm-ok="Ya, sudah direfund">
            @csrf
            <button type="submit" class="admin-btn admin-btn-primary">Tandai sudah direfund</button>
        </form>
    </div>
@endif

    <div class="admin-card mb-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="mb-1 text-xs text-admin-muted">
                    {{ ($order->placed_at ?? $order->created_at)->format('d M Y, H:i') }}
                </p>
                <h1 class="text-xl font-semibold text-admin-text">
                    Pesanan <span class="font-mono text-admin-accent">#{{ $order->order_number }}</span>
                </h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="admin-badge {{ $order->statusBadgeClass() }}">Status: {{ $order->statusLabel() }}</span>
                <span class="admin-badge {{ $order->paymentStatusBadgeClass() }}">Pembayaran: {{ $order->paymentStatusLabel() }}</span>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto border-t border-admin-border pt-5">
            <ol class="flex min-w-max items-center gap-2">
                @foreach ($order->trackingTimeline() as $step)
                    <li class="flex items-center gap-2">
                        <div class="flex w-24 flex-col items-center">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full border text-[10px] font-bold
                                {{ $step['is_completed'] ? 'border-admin-accent bg-admin-accent text-white' : 'border-admin-border bg-white text-admin-muted' }}">
                                <span class="sr-only">{{ $step['is_completed'] ? 'Selesai: ' : 'Belum: ' }}</span>
                                {{ $loop->iteration }}
                            </div>
                            <p class="mt-1.5 text-center text-[10px] leading-tight {{ $step['is_current'] ? 'font-bold text-admin-accent' : ($step['is_completed'] ? 'font-medium text-admin-text' : 'text-admin-muted') }}">
                                {{ $step['title'] }}
                            </p>
                        </div>
                        @if (! $loop->last)
                            <div class="h-px w-8 {{ $step['is_completed'] ? 'bg-admin-accent' : 'bg-admin-border' }}" aria-hidden="true"></div>
                        @endif
                    </li>
                @endforeach
            </ol>
            @if ($order->status === 'cancelled')
                <p class="mt-3 text-xs font-medium text-admin-danger">Pesanan ini telah dibatalkan.</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="admin-card overflow-hidden">
                <div class="border-b border-admin-border px-6 py-4">
                    <h2 class="text-sm font-semibold text-admin-text">Item pesanan</h2>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Produk / varian</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Harga satuan</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>
                                        <p class="font-medium text-admin-text">{{ $item->product_name }}</p>
                                        <p class="text-xs text-admin-muted">{{ $item->variant_name }} · SKU: {{ $item->sku }}</p>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right text-admin-muted">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-right font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50">
                            <tr>
                                <td colspan="3" class="px-4 py-2.5 text-right text-xs text-admin-muted">Subtotal pesanan</td>
                                <td class="px-4 py-2.5 text-right font-medium">{{ $order->formattedSubtotal() }}</td>
                            </tr>
                            @if ((float) $order->discount_amount > 0)
                                <tr>
                                    <td colspan="3" class="px-4 py-2.5 text-right text-xs text-admin-muted">Diskon {{ $order->voucher_code ? '(' . $order->voucher_code . ')' : '' }}</td>
                                    <td class="px-4 py-2.5 text-right font-medium text-admin-success">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="px-4 py-2.5 text-right text-xs text-admin-muted">Ongkos kirim</td>
                                <td class="px-4 py-2.5 text-right font-medium">{{ $order->formattedShippingCost() }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right text-sm font-bold">Grand total</td>
                                <td class="px-4 py-3 text-right text-base font-bold text-admin-text">{{ $order->formattedGrandTotal() }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="admin-card p-6">
                <h2 class="mb-4 text-sm font-semibold text-admin-text">Informasi pelanggan &amp; pengiriman</h2>
                <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <p class="mb-1 text-xs uppercase tracking-wide text-admin-muted">Akun pelanggan</p>
                        <p class="font-medium text-admin-text">{{ $order->user?->name ?? '—' }}</p>
                        <p class="text-xs text-admin-muted">{{ $order->user?->email ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="mb-1 text-xs uppercase tracking-wide text-admin-muted">Penerima</p>
                        <p class="font-medium text-admin-text">{{ $order->shipping_recipient_name }}</p>
                        <p class="text-xs text-admin-muted">{{ $order->shipping_phone }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="mb-1 text-xs uppercase tracking-wide text-admin-muted">Alamat kirim</p>
                        <p class="text-admin-text">{{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}</p>
                        @if ($order->customer_note)
                            <p class="mt-2 text-xs italic text-admin-muted">Catatan: "{{ $order->customer_note }}"</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="admin-card p-6">
                <h2 class="mb-1 text-sm font-semibold text-admin-text">Aksi status</h2>
                <p class="mb-4 text-xs text-admin-muted">Hanya transisi yang valid menurut state machine yang ditampilkan.</p>

                @if (! empty($allowedNextStatuses))
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-3"
                        data-confirm="Perbarui status pesanan ini?"
                        data-confirm-title="Ubah status"
                        data-confirm-ok="Ya, perbarui">
                        @csrf
                        @method('PATCH')
                        <label for="next-status" class="admin-label">Status berikutnya</label>
                        <select id="next-status" name="status" required class="admin-input">
                            <option value="" disabled selected>Pilih status berikutnya…</option>
                            @foreach ($allowedNextStatuses as $next)
                                <option value="{{ $next }}">
                                    {{ match ($next) {
                                        'confirmed' => 'Terkonfirmasi',
                                        'processing' => 'Diproses',
                                        'packed' => 'Dikemas',
                                        'shipped' => 'Dikirim',
                                        'delivered' => 'Tiba di tujuan',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                        default => ucfirst($next),
                                    } }}
                                </option>
                            @endforeach
                        </select>
                        <label for="status-note" class="admin-label">Catatan</label>
                        <input id="status-note" type="text" name="note" maxlength="255" placeholder="Catatan opsional…" class="admin-input">
                        <button type="submit" class="admin-btn admin-btn-primary w-full">Perbarui status</button>
                    </form>
                @else
                    <p class="text-xs italic text-admin-muted">Pesanan sudah berada pada status akhir ({{ $order->statusLabel() }}). Tidak ada transisi lanjutan.</p>
                @endif

                @if (in_array($order->status, ['pending', 'confirmed', 'processing', 'packed'], true))
                    <div class="mt-4 border-t border-admin-border pt-4">
                        <form method="POST" action="{{ route('admin.orders.cancel', $order) }}"
                            data-confirm="Batalkan pesanan ini? Stok produk akan dikembalikan."
                            data-confirm-title="Batalkan pesanan"
                            data-confirm-ok="Ya, batalkan">
                            @csrf
                            <input type="hidden" name="reason" value="Dibatalkan oleh administrator">
                            <button type="submit" class="admin-btn admin-btn-danger w-full">Batalkan pesanan</button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="admin-card p-6">
                <h2 class="mb-4 text-sm font-semibold text-admin-text">Detail pembayaran</h2>
                @forelse ($order->payments as $payment)
                    <div class="mb-3 space-y-1 rounded-lg border border-admin-border bg-admin-bg p-3 text-xs last:mb-0">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-semibold text-admin-text">{{ $payment->provider }} · {{ $payment->payment_method }}</span>
                            <span class="admin-badge {{ $payment->statusBadgeClass() }}">{{ $payment->statusLabel() }}</span>
                        </div>
                        <p class="text-admin-muted">Ref: <span class="font-mono">{{ $payment->transaction_reference ?? '—' }}</span></p>
                        <p class="text-admin-muted">Nominal: <span class="font-semibold text-admin-text">{{ $payment->formattedAmount() }}</span></p>
                        @if ($payment->paid_at)
                            <p class="text-admin-muted">Dibayar: {{ $payment->paid_at->format('d M Y, H:i') }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-xs italic text-admin-muted">Belum ada record pembayaran.</p>
                @endforelse
            </div>

            <div class="admin-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-admin-text">Detail pengiriman</h2>
                    @if ($order->shipment)
                        <span class="admin-badge {{ $order->shipment->statusBadgeClass() }}">
                            {{ $order->shipment->statusLabel() }}
                        </span>
                    @endif
                </div>
                @if ($order->shipment && $order->shipment->destination_id)
                    <dl class="mb-4 grid grid-cols-2 gap-3 rounded-lg border border-admin-border bg-admin-bg/60 p-3 text-xs">
                        <div><dt class="text-admin-muted">ID tujuan RajaOngkir</dt><dd class="font-medium text-admin-text">{{ $order->shipment->destination_id }}</dd></div>
                        <div><dt class="text-admin-muted">Kode kurir</dt><dd class="font-medium uppercase text-admin-text">{{ $order->shipment->courier_code }}</dd></div>
                        <div><dt class="text-admin-muted">Berat paket</dt><dd class="font-medium text-admin-text">{{ number_format($order->shipment->weight_gram, 0, ',', '.') }} gram</dd></div>
                        <div><dt class="text-admin-muted">Estimasi API</dt><dd class="font-medium text-admin-text">{{ $order->shipment->estimated_delivery ?: 'Tidak tersedia' }}</dd></div>
                    </dl>
                @endif
                <form method="POST" action="{{ route('admin.orders.shipment', $order) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="courier" class="admin-label">Kurir</label>
                        <input id="courier" type="text" name="courier" value="{{ old('courier', $order->shipment?->courier) }}" class="admin-input" placeholder="Contoh: JNE, J&T, SiCepat">
                    </div>
                    <div>
                        <label for="service" class="admin-label">Layanan</label>
                        <input id="service" type="text" name="service" value="{{ old('service', $order->shipment?->service) }}" class="admin-input" placeholder="Contoh: Reguler, YES, dsb.">
                    </div>
                    <div>
                        <label for="tracking_number" class="admin-label">Nomor resi</label>
                        <input id="tracking_number" type="text" name="tracking_number" value="{{ old('tracking_number', $order->shipment?->tracking_number) }}" class="admin-input font-mono" placeholder="Masukkan nomor resi...">
                    </div>
                    <div>
                        <label for="shipping_cost" class="admin-label">Biaya pengiriman</label>
                        <input id="shipping_cost" type="number" step="0.01" name="shipping_cost" value="{{ old('shipping_cost', $order->shipment?->shipping_cost) }}" class="admin-input">
                    </div>
                    <div>
                        <label for="shipment-status" class="admin-label">Status pengiriman</label>
                        <select id="shipment-status" name="status" class="admin-input">
                            <option value="">-- Pilih status --</option>
                            <option value="pending" {{ $order->shipment?->status == 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                            <option value="picked_up" {{ $order->shipment?->status == 'picked_up' ? 'selected' : '' }}>Diambil Kurir (Picked up)</option>
                            <option value="in_transit" {{ $order->shipment?->status == 'in_transit' ? 'selected' : '' }}>Dalam Perjalanan (In transit)</option>
                            <option value="shipped" {{ $order->shipment?->status == 'shipped' ? 'selected' : '' }}>Dikirim (Shipped)</option>
                            <option value="delivered" {{ $order->shipment?->status == 'delivered' ? 'selected' : '' }}>Tiba di Tujuan (Delivered)</option>
                            <option value="returned" {{ $order->shipment?->status == 'returned' ? 'selected' : '' }}>Dikembalikan (Returned)</option>
                            <option value="cancelled" {{ $order->shipment?->status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                    <button type="submit" class="admin-btn admin-btn-primary w-full">{{ $order->shipment ? 'Perbarui pengiriman' : 'Simpan pengiriman' }}</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
