<x-layouts.admin>
    <x-slot:title>Pesanan - MH Scarf Admin</x-slot:title>
    <x-slot:header>Pesanan</x-slot:header>

    <x-admin.page-header
        title="Daftar Pesanan"
        description="Pantau pembayaran dan status pesanan. Perubahan status mengikuti alur yang sudah ditetapkan sistem."
    />

    @php
        $statusOptions = [
            'pending' => 'Menunggu pembayaran',
            'confirmed' => 'Terkonfirmasi',
            'processing' => 'Diproses',
            'packed' => 'Dikemas',
            'shipped' => 'Dikirim',
            'delivered' => 'Tiba di tujuan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
        $paymentOptions = [
            'pending' => 'Belum dibayar',
            'paid' => 'Sudah dibayar',
            'failed' => 'Gagal',
            'expired' => 'Kedaluwarsa',
            'refunded' => 'Dikembalikan',
        ];
    @endphp

    <div class="admin-card mb-6 p-4">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-4">
            <div class="relative sm:col-span-2 admin-search-wrap">
                <label for="order-search" class="sr-only">Cari pesanan</label>
                <input id="order-search" type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nomor pesanan, nama penerima, atau pelanggan..."
                    class="admin-input pl-10">
                <svg class="admin-search-icon pointer-events-none absolute left-3.5 top-[calc(50%+2px)] -translate-y-1/2 h-4 w-4 text-admin-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <div>
                <label for="order-status" class="sr-only">Status pesanan</label>
                <select id="order-status" name="status" class="admin-input">
                    <option value="">Semua status pesanan</option>
                    @foreach ($statusOptions as $st => $label)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <label for="order-payment" class="sr-only">Status pembayaran</label>
                <select id="order-payment" name="payment_status" class="admin-input">
                    <option value="">Semua status bayar</option>
                    @foreach ($paymentOptions as $pst => $label)
                        <option value="{{ $pst }}" {{ request('payment_status') === $pst ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn admin-btn-dark shrink-0">Filter</button>
                @if (request()->hasAny(['search', 'status', 'payment_status']))
                    <a href="{{ route('admin.orders.index') }}" class="admin-btn admin-btn-secondary shrink-0">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="admin-card overflow-hidden">
        @if ($orders->isEmpty())
            <x-admin.empty-state
                title="Belum ada pesanan"
                description="Data pesanan pelanggan akan muncul di sini setelah checkout."
            />
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>No. pesanan</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="whitespace-nowrap font-mono text-xs font-semibold text-admin-accent">{{ $order->order_number }}</td>
                                <td>
                                    <p class="font-medium text-admin-text">{{ $order->shipping_recipient_name }}</p>
                                    <p class="text-xs text-admin-muted">{{ $order->user?->name ?? 'Pelanggan dihapus' }}</p>
                                </td>
                                <td class="whitespace-nowrap text-xs text-admin-muted">
                                    {{ ($order->placed_at ?? $order->created_at)->format('d M Y, H:i') }}
                                </td>
                                <td>
                                    <span class="admin-badge {{ $order->paymentStatusBadgeClass() }}">{{ $order->paymentStatusLabel() }}</span>
                                </td>
                                <td>
                                    <span class="admin-badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span>
                                </td>
                                <td class="whitespace-nowrap text-right font-semibold">{{ $order->formattedGrandTotal() }}</td>
                                <td class="whitespace-nowrap text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="admin-btn admin-btn-secondary px-3 py-1.5 text-xs">Kelola</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($orders->hasPages())
                <div class="border-t border-admin-border p-4">
                    {{ $orders->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.admin>
