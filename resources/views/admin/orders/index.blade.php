<x-layouts.admin>
    <x-slot:title>Pesanan - MH Scarf Admin</x-slot:title>
    <x-slot:header>Manajemen Pesanan</x-slot:header>

    <div class="mb-6">
        <h1 class="font-serif-display text-2xl font-bold text-[#3A3033]">Daftar Pesanan</h1>
        <p class="text-xs text-[#75686D] mt-0.5">Pantau dan kelola status pesanan pelanggan sesuai alur state machine</p>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-[#EBDDE2] mb-6 shadow-xs">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nomor pesanan, nama penerima, atau pelanggan..."
                    class="w-full pl-9 pr-4 py-2 rounded-xl border border-[#EBDDE2] text-sm text-[#3A3033] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-[#FFF9F5]/40">
                <svg class="w-4 h-4 text-[#75686D] absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div>
                <select name="status" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-xl border border-[#EBDDE2] text-sm text-[#3A3033] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-white">
                    <option value="">Semua Status Pesanan</option>
                    @foreach (['pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'completed', 'cancelled'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="payment_status" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-xl border border-[#EBDDE2] text-sm text-[#3A3033] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-white">
                    <option value="">Semua Status Bayar</option>
                    @foreach (['pending', 'paid', 'failed', 'expired', 'refunded'] as $pst)
                        <option value="{{ $pst }}" {{ request('payment_status') === $pst ? 'selected' : '' }}>{{ ucfirst($pst) }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-[#EBDDE2] shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#EBDDE2] text-sm">
                <thead class="bg-[#FFF9F5]">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#75686D]">No. Pesanan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#75686D]">Pelanggan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#75686D]">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#75686D]">Pembayaran</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#75686D]">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#75686D]">Total</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#75686D]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EBDDE2]">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-[#FFF9F5]/60 transition">
                            <td class="px-4 py-3 font-mono text-xs text-[#D98FAF] font-semibold whitespace-nowrap">{{ $order->order_number }}</td>
                            <td class="px-4 py-3">
                                <p class="text-[#3A3033] font-medium">{{ $order->shipping_recipient_name }}</p>
                                <p class="text-xs text-[#75686D]">{{ $order->user?->name ?? 'Pelanggan dihapus' }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-[#75686D] whitespace-nowrap">
                                {{ ($order->placed_at ?? $order->created_at)->format('d M Y, H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $order->paymentStatusBadgeClass() }}">
                                    {{ $order->paymentStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $order->statusBadgeClass() }}">
                                    {{ $order->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-[#3A3033] font-semibold whitespace-nowrap">{{ $order->formattedGrandTotal() }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium border border-[#EBDDE2] text-[#3A3033] hover:bg-[#FFF9F5] transition">
                                    Kelola
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-[#75686D]">
                                ❀ Belum ada pesanan. Data pesanan pelanggan akan muncul di sini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="p-4 border-t border-[#EBDDE2]">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
