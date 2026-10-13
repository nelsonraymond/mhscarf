<x-layouts.admin>
    <x-slot:title>Pesanan #{{ $order->order_number }} - MH Scarf Admin</x-slot:title>
    <x-slot:header>Detail Pesanan</x-slot:header>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Header -->
    <div class="bg-white rounded-2xl border border-[#EBDDE2] p-6 shadow-xs mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs text-[#75686D] mb-1">
                    {{ ($order->placed_at ?? $order->created_at)->format('d M Y, H:i') }}
                </p>
                <h1 class="font-serif-display text-xl font-bold text-[#3A3033]">
                    Pesanan <span class="font-mono text-[#D98FAF]">#{{ $order->order_number }}</span>
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

        <!-- Timeline -->
        <div class="mt-6 pt-5 border-t border-[#EBDDE2] overflow-x-auto">
            <div class="flex items-center min-w-max space-x-2">
                @foreach ($order->trackingTimeline() as $i => $step)
                    <div class="flex items-center space-x-2">
                        <div class="flex flex-col items-center w-24">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold border
                                {{ $step['is_completed'] ? 'bg-[#D98FAF] border-[#D98FAF] text-white' : 'bg-white border-[#EBDDE2] text-[#75686D]' }}">
                                ✓
                            </div>
                            <p class="mt-1.5 text-[10px] text-center leading-tight {{ $step['is_current'] ? 'text-[#D98FAF] font-bold' : ($step['is_completed'] ? 'text-[#3A3033] font-medium' : 'text-[#75686D]') }}">
                                {{ $step['title'] }}
                            </p>
                        </div>
                        @if (! $loop->last)
                            <div class="w-8 h-px {{ $step['is_completed'] ? 'bg-[#D98FAF]' : 'bg-[#EBDDE2]' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
            @if ($order->status === 'cancelled')
                <p class="mt-3 text-xs text-rose-600 font-medium">⛔ Pesanan ini telah dibatalkan.</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left column: items + customer -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Items -->
            <div class="bg-white rounded-2xl border border-[#EBDDE2] shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-[#EBDDE2]">
                    <h2 class="text-sm font-semibold text-[#3A3033]">Item Pesanan</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#EBDDE2] text-sm">
                        <thead class="bg-[#FFF9F5]">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#75686D]">Produk / Varian</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-[#75686D]">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#75686D]">Harga Satuan</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#75686D]">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EBDDE2]">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="text-[#3A3033] font-medium">{{ $item->product_name }}</p>
                                        <p class="text-xs text-[#75686D]">{{ $item->variant_name }} · SKU: {{ $item->sku }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-center text-[#3A3033]">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right text-[#75686D]">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right text-[#3A3033] font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-[#FFF9F5]">
                            <tr>
                                <td colspan="3" class="px-4 py-2.5 text-right text-xs text-[#75686D]">Subtotal pesanan</td>
                                <td class="px-4 py-2.5 text-right text-[#3A3033] font-medium">{{ $order->formattedSubtotal() }}</td>
                            </tr>
                            @if ((float) $order->discount_amount > 0)
                                <tr>
                                    <td colspan="3" class="px-4 py-2.5 text-right text-xs text-[#75686D]">Diskon {{ $order->voucher_code ? "( {$order->voucher_code} )" : '' }}</td>
                                    <td class="px-4 py-2.5 text-right text-emerald-600 font-medium">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="px-4 py-2.5 text-right text-xs text-[#75686D]">Ongkos kirim</td>
                                <td class="px-4 py-2.5 text-right text-[#3A3033] font-medium">{{ $order->formattedShippingCost() }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right text-sm font-bold text-[#3A3033]">Grand Total</td>
                                <td class="px-4 py-3 text-right font-serif-display text-base font-bold text-[#D98FAF]">{{ $order->formattedGrandTotal() }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Customer & Shipping Info -->
            <div class="bg-white rounded-2xl border border-[#EBDDE2] p-6 shadow-xs">
                <h2 class="text-sm font-semibold text-[#3A3033] mb-4">Informasi Pelanggan &amp; Pengiriman</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-[#75686D] mb-1">Akun Pelanggan</p>
                        <p class="text-[#3A3033] font-medium">{{ $order->user?->name ?? '—' }}</p>
                        <p class="text-xs text-[#75686D]">{{ $order->user?->email ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-[#75686D] mb-1">Penerima</p>
                        <p class="text-[#3A3033] font-medium">{{ $order->shipping_recipient_name }}</p>
                        <p class="text-xs text-[#75686D]">{{ $order->shipping_phone }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs uppercase tracking-wide text-[#75686D] mb-1">Alamat Kirim</p>
                        <p class="text-[#3A3033]">{{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}</p>
                        @if ($order->customer_note)
                            <p class="mt-2 text-xs text-[#75686D] italic">Catatan: "{{ $order->customer_note }}"</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right column: actions, payment, shipment -->
        <div class="space-y-6">
            <!-- Status Transition Actions -->
            <div class="bg-white rounded-2xl border border-[#EBDDE2] p-6 shadow-xs">
                <h2 class="text-sm font-semibold text-[#3A3033] mb-1">Aksi Status</h2>
                <p class="text-xs text-[#75686D] mb-4">Perubahan status divalidasi server-side sesuai state machine pesanan.</p>

                @if (! empty($allowedNextStatuses))
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <select name="status" required
                            class="w-full px-3 py-2 rounded-xl border border-[#EBDDE2] text-sm text-[#3A3033] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-white">
                            <option value="" disabled selected>Pilih status berikutnya…</option>
                            @foreach ($allowedNextStatuses as $next)
                                <option value="{{ $next }}">
                                    {{ match ($next) {
                                        'confirmed' => 'Terkonfirmasi',
                                        'processing' => 'Diproses',
                                        'packed' => 'Dikemas',
                                        'shipped' => 'Dikirim',
                                        'delivered' => 'Tiba di Tujuan',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                        default => ucfirst($next),
                                    } }}
                                </option>
                            @endforeach
                        </select>
                        <input type="text" name="note" maxlength="255" placeholder="Catatan opsional…"
                            class="w-full px-3 py-2 rounded-xl border border-[#EBDDE2] text-sm focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-[#FFF9F5]/40">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl font-medium text-white bg-[#D98FAF] hover:bg-[#B97897] shadow-xs transition text-sm cursor-pointer">
                            Perbarui Status
                        </button>
                    </form>
                @else
                    <p class="text-xs text-[#75686D] italic">Pesanan sudah berada pada status akhir ({{ $order->statusLabel() }}). Tidak ada transisi lanjutan.</p>
                @endif

                @if (in_array($order->status, ['pending', 'confirmed', 'processing', 'packed'], true))
                    <div class="mt-4 pt-4 border-t border-[#EBDDE2]">
                        <form method="POST" action="{{ route('admin.orders.cancel', $order) }}"
                            onsubmit="return confirm('Batalkan pesanan ini? Stok produk akan dikembalikan.');">
                            @csrf
                            <input type="hidden" name="reason" value="Dibatalkan oleh administrator">
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl font-medium text-sm text-rose-700 border border-rose-200 bg-rose-50 hover:bg-rose-100 transition cursor-pointer">
                                Batalkan Pesanan
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Payment Details -->
            <div class="bg-white rounded-2xl border border-[#EBDDE2] p-6 shadow-xs">
                <h2 class="text-sm font-semibold text-[#3A3033] mb-4">Detail Pembayaran</h2>
                @forelse ($order->payments as $payment)
                    <div class="mb-3 last:mb-0 p-3 rounded-xl bg-[#FFF9F5] border border-[#EBDDE2] text-xs space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-[#3A3033]">{{ $payment->provider }} · {{ $payment->payment_method }}</span>
                            <span class="px-2 py-0.5 rounded-full font-semibold border {{ $payment->statusBadgeClass() }}">{{ $payment->statusLabel() }}</span>
                        </div>
                        <p class="text-[#75686D]">Ref: <span class="font-mono">{{ $payment->transaction_reference ?? '—' }}</span></p>
                        <p class="text-[#75686D]">Nominal: <span class="text-[#3A3033] font-semibold">{{ $payment->formattedAmount() }}</span></p>
                        @if ($payment->paid_at)
                            <p class="text-[#75686D]">Dibayar: {{ $payment->paid_at->format('d M Y, H:i') }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-[#75686D] italic">Belum ada record pembayaran.</p>
                @endforelse
            </div>

            <!-- Shipment Details -->
            <div class="bg-white rounded-2xl border border-[#EBDDE2] p-6 shadow-xs">
                <h2 class="text-sm font-semibold text-[#3A3033] mb-4">Detail Pengiriman</h2>

                @if ($order->shipment)
                                            <form method="POST" action="{{ route('admin.orders.shipment', $order) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <div class="flex flex-col space-y-2">
                            <label class="text-sm font-medium text-[#75686D]">Kurir</label>
                            <input type="text" name="courier" value="{{ old('courier', $order->shipment->courier) }}" class="w-full px-3 py-2 rounded-xl border border-[#EBDDE2] text-sm focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-white">
                        </div>
                        <div class="flex flex-col space-y-2">
                            <label class="text-sm font-medium text-[#75686D]">Layanan</label>
                            <input type="text" name="service" value="{{ old('service', $order->shipment->service) }}" class="w-full px-3 py-2 rounded-xl border border-[#EBDDE2] text-sm focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-white">
                        </div>
                        <div class="flex flex-col space-y-2">
                            <label class="text-sm font-medium text-[#75686D]">Nomor Resi</label>
                            <input type="text" name="tracking_number" value="{{ old('tracking_number', $order->shipment->tracking_number) }}" class="w-full px-3 py-2 rounded-xl border border-[#EBDDE2] text-sm focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-white">
                        </div>
                        <div class="flex flex-col space-y-2">
                            <label class="text-sm font-medium text-[#75686D]">Biaya Pengiriman</label>
                            <input type="number" step="0.01" name="shipping_cost" value="{{ old('shipping_cost', $order->shipment->shipping_cost) }}" class="w-full px-3 py-2 rounded-xl border border-[#EBDDE2] text-sm focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-white">
                        </div>
                        <div class="flex flex-col space-y-2">
                            <label class="text-sm font-medium text-[#75686D]">Status</label>
                            <select name="status" class="w-full px-3 py-2 rounded-xl border border-[#EBDDE2] text-sm focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-white">
                                <option value="">-- Pilih Status --</option>
                                <option value="pending" {{ $order->shipment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="shipped" {{ $order->shipment->status == 'shipped' ? 'selected' : '' }}>Dikirim</option>
                                <option value="delivered" {{ $order->shipment->status == 'delivered' ? 'selected' : '' }}>Tiba</option>
                                <option value="cancelled" {{ $order->shipment->status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl font-medium text-white bg-[#D98FAF] hover:bg-[#B97897] shadow-xs transition text-sm cursor-pointer">
                            Perbarui Pengiriman
                        </button>
                    </form>
                @else
                    <p class="text-xs text-[#75686D] italic">Data pengiriman belum dibuat.</p>
                @endif
            </div>
{{--
                <h2 class="text-sm font-semibold text-[#3A3033] mb-4">Detail Pengiriman</h2>
                @if ($order->shipment)
                    <div class="text-xs space-y-1.5">
                        <p class="text-[#75686D]">Kurir: <span class="text-[#3A3033] font-semibold">{{ $order->shipment->courier ?? '—' }} {{ $order->shipment->service ? '('.$order->shipment->service.')' : '' }}</span></p>
                        <p class="text-[#75686D]">Resi: <span class="font-mono text-[#3A3033]">{{ $order->shipment->tracking_number ?? 'belum tersedia' }}</span></p>
                        <p class="text-[#75686D]">Biaya: <span class="text-[#3A3033]">Rp {{ number_format($order->shipment->shipping_cost, 0, ',', '.') }}</span></p>
                        <p class="text-[#75686D]">Status: <span class="px-2 py-0.5 rounded-full font-semibold border bg-stone-50 text-stone-700 border-stone-200">{{ ucfirst($order->shipment->status ?? 'pending') }}</span></p>
                        @if ($order->shipment->shipped_at)
                            <p class="text-[#75686D]">Dikirim: {{ $order->shipment->shipped_at->format('d M Y, H:i') }}</p>
                        @endif
                        @if ($order->shipment->delivered_at)
                            <p class="text-[#75686D]">Sampai: {{ $order->shipment->delivered_at->format('d M Y, H:i') }}</p>
                        @endif
                    </div>
                @else
                    <p class="text-xs text-[#75686D] italic">Data pengiriman belum dibuat.</p>
                @endif --}}
            </div>
        </div>
    </div>
</x-layouts.admin>
