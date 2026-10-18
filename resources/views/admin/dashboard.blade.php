<x-layouts.admin>
    <x-slot:title>Dashboard - MH Scarf Admin</x-slot:title>
    <x-slot:header>Dashboard</x-slot:header>

    <x-admin.page-header
        title="Admin Dashboard"
        description="Ringkasan operasional MH Scarf. Angka pendapatan hanya dari pesanan berstatus pembayaran lunas dan tidak dibatalkan."
    />

    @if ($refundCount > 0)
    <section class="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-5" aria-labelledby="refund-heading" role="alert">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 id="refund-heading" class="text-sm font-semibold text-amber-900">
                    {{ $refundCount }} pesanan perlu direfund
                </h2>
                <p class="mt-0.5 text-xs text-amber-800">Pembayaran sudah diterima, tetapi pesanannya dibatalkan. Kembalikan dana di dashboard Midtrans, lalu tandai di sini.</p>
            </div>
            <a href="{{ route('admin.orders.index', ['needs_refund' => 1]) }}" class="text-xs font-medium text-amber-900 underline hover:no-underline">Lihat semua</a>
        </div>

        <ul class="mt-3 divide-y divide-amber-200 rounded-lg border border-amber-200 bg-white/70">
            @foreach ($refundOrders as $refundOrder)
                <li>
                    <a href="{{ route('admin.orders.show', $refundOrder) }}" class="flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-white">
                        <div class="min-w-0">
                            <p class="truncate font-mono text-xs font-semibold text-admin-accent">{{ $refundOrder->order_number }}</p>
                            <p class="truncate text-xs text-admin-muted">{{ $refundOrder->shipping_recipient_name }}</p>
                        </div>
                        <span class="shrink-0 text-sm font-semibold tabular-nums text-admin-text">{{ $refundOrder->formattedGrandTotal() }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
@endif

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.orders.index') }}" class="admin-card p-5 transition hover:border-admin-accent/40">
            <p class="text-xs font-medium uppercase tracking-wide text-admin-muted">Total pesanan</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-admin-text">{{ number_format($totalOrders) }}</p>
            <p class="mt-1 text-xs text-admin-muted">Semua status, termasuk pending</p>
        </a>
        <a href="{{ route('admin.orders.index', ['payment_status' => 'pending']) }}" class="admin-card p-5 transition hover:border-admin-accent/40">
            <p class="text-xs font-medium uppercase tracking-wide text-admin-muted">Menunggu pembayaran</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-admin-text">{{ number_format($awaitingPayment) }}</p>
            <p class="mt-1 text-xs text-admin-muted">Belum lunas dan belum dibatalkan</p>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="admin-card p-5 transition hover:border-admin-accent/40">
            <p class="text-xs font-medium uppercase tracking-wide text-admin-muted">Perlu diproses</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-admin-text">{{ number_format($needsFulfillment) }}</p>
            <p class="mt-1 text-xs text-admin-muted">Terkonfirmasi, diproses, atau dikemas</p>
        </a>
        <div class="admin-card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-admin-muted">Pendapatan lunas</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-admin-text">Rp {{ number_format($paidRevenue, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-admin-muted">Pembayaran paid, status bukan cancelled</p>
        </div>
        <a href="{{ route('admin.products.index', ['status' => 'active']) }}" class="admin-card p-5 transition hover:border-admin-accent/40">
            <p class="text-xs font-medium uppercase tracking-wide text-admin-muted">Produk aktif</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-admin-text">{{ number_format($activeProducts) }}</p>
            <p class="mt-1 text-xs text-admin-muted">Ditampilkan di etalase</p>
        </a>
        <a href="{{ route('admin.products.index') }}" class="admin-card p-5 transition hover:border-admin-accent/40">
            <p class="text-xs font-medium uppercase tracking-wide text-admin-muted">Unit stok</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-admin-text">{{ number_format($stockUnits) }}</p>
            <p class="mt-1 text-xs text-admin-muted">{{ $lowStockCount }} varian stok rendah / habis</p>
        </a>
        <a href="{{ route('admin.reviews.index', ['visibility' => 'hidden']) }}" class="admin-card p-5 transition hover:border-admin-accent/40">
            <p class="text-xs font-medium uppercase tracking-wide text-admin-muted">Ulasan tersembunyi</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-admin-text">{{ number_format($unpublishedReviews) }}</p>
            <p class="mt-1 text-xs text-admin-muted">Perlu ditinjau jika ada</p>
        </a>
        <div class="admin-card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-admin-muted">Akun admin</p>
            <p class="mt-2 truncate text-base font-semibold text-admin-text">{{ $admin->name }}</p>
            <p class="mt-1 truncate text-xs text-admin-muted">{{ $admin->email }} · {{ $admin->role }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="admin-card xl:col-span-2" aria-labelledby="recent-orders-heading">
            <div class="flex items-center justify-between border-b border-admin-border px-5 py-4">
                <h2 id="recent-orders-heading" class="text-sm font-semibold text-admin-text">Pesanan terbaru</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-medium text-admin-accent hover:underline">Lihat semua</a>
            </div>
            @if ($recentOrders->isEmpty())
                <x-admin.empty-state
                    title="Belum ada pesanan"
                    description="Pesanan pelanggan akan muncul di sini setelah checkout."
                    action-href="{{ route('admin.orders.index') }}"
                    action-label="Buka halaman pesanan"
                />
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>No. pesanan</th>
                                <th>Pelanggan</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs font-semibold text-admin-accent hover:underline">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <p class="font-medium text-admin-text">{{ $order->shipping_recipient_name }}</p>
                                        <p class="text-xs text-admin-muted">{{ $order->user?->name ?? 'Pelanggan dihapus' }}</p>
                                    </td>
                                    <td class="whitespace-nowrap text-xs text-admin-muted">
                                        {{ ($order->placed_at ?? $order->created_at)->format('d M Y, H:i') }}
                                    </td>
                                    <td>
                                        <span class="admin-badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span>
                                    </td>
                                    <td class="text-right font-medium whitespace-nowrap">{{ $order->formattedGrandTotal() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="admin-card" aria-labelledby="actionable-orders-heading">
            <div class="border-b border-admin-border px-5 py-4">
                <h2 id="actionable-orders-heading" class="text-sm font-semibold text-admin-text">Perlu ditindaklanjuti</h2>
                <p class="mt-0.5 text-xs text-admin-muted">Pembayaran tertunda atau pesanan yang menunggu pemrosesan.</p>
            </div>
            @if ($actionableOrders->isEmpty())
                <x-admin.empty-state title="Tidak ada antrean" description="Semua pesanan sudah ditindaklanjuti." />
            @else
                <ul class="divide-y divide-admin-border">
                    @foreach ($actionableOrders as $order)
                        <li class="px-5 py-3">
                            <a href="{{ route('admin.orders.show', $order) }}" class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-mono text-xs font-semibold text-admin-accent">{{ $order->order_number }}</p>
                                    <p class="truncate text-sm text-admin-text">{{ $order->shipping_recipient_name }}</p>
                                </div>
                                <span class="admin-badge shrink-0 {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="admin-card" aria-labelledby="top-products-heading">
            <div class="border-b border-admin-border px-5 py-4">
                <h2 id="top-products-heading" class="text-sm font-semibold text-admin-text">Produk terlaris</h2>
                <p class="mt-0.5 text-xs text-admin-muted">Berdasarkan unit terjual pada pesanan lunas.</p>
            </div>
            @if ($topProducts->isEmpty())
                <x-admin.empty-state
                    title="Belum ada penjualan lunas"
                    description="Tambahkan produk lalu proses pesanan yang sudah dibayar."
                    action-href="{{ route('admin.products.create') }}"
                    action-label="Tambah produk"
                />
            @else
                <ul class="divide-y divide-admin-border">
                    @foreach ($topProducts as $item)
                        <li class="flex items-center justify-between gap-3 px-5 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-admin-text">{{ $item->product_name }}</p>
                                <p class="text-xs text-admin-muted">{{ number_format((int) $item->units_sold) }} unit</p>
                            </div>
                            <p class="shrink-0 text-sm font-medium tabular-nums">Rp {{ number_format((float) $item->revenue, 0, ',', '.') }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="admin-card" aria-labelledby="low-stock-heading">
            <div class="flex items-center justify-between border-b border-admin-border px-5 py-4">
                <div>
                    <h2 id="low-stock-heading" class="text-sm font-semibold text-admin-text">Stok rendah</h2>
                    <p class="mt-0.5 text-xs text-admin-muted">Stok 0 atau di bawah ambang varian.</p>
                </div>
                <a href="{{ route('admin.products.index') }}" class="text-xs font-medium text-admin-accent hover:underline">Produk</a>
            </div>
            @if ($lowStockVariants->isEmpty())
                <x-admin.empty-state title="Stok aman" description="Tidak ada varian di bawah ambang stok rendah." />
            @else
                <ul class="divide-y divide-admin-border">
                    @foreach ($lowStockVariants as $variant)
                        <li class="flex items-center justify-between gap-3 px-5 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-admin-text">{{ $variant->product?->name ?? 'Produk dihapus' }}</p>
                                <p class="truncate text-xs text-admin-muted">{{ $variant->name }} · {{ $variant->sku }}</p>
                            </div>
                            <span class="admin-badge border-rose-200 bg-rose-50 text-rose-800">{{ $variant->stock_qty }} pcs</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="admin-card" aria-labelledby="payment-summary-heading">
            <div class="border-b border-admin-border px-5 py-4">
                <h2 id="payment-summary-heading" class="text-sm font-semibold text-admin-text">Ringkasan pembayaran</h2>
            </div>
            @php
                $paymentLabels = [
                    'pending' => 'Belum dibayar',
                    'paid' => 'Sudah dibayar',
                    'failed' => 'Gagal',
                    'expired' => 'Kedaluwarsa',
                    'refunded' => 'Dikembalikan',
                ];
            @endphp
            @if ($paymentBreakdown->isEmpty())
                <x-admin.empty-state title="Belum ada data pembayaran" />
            @else
                <ul class="divide-y divide-admin-border">
                    @foreach ($paymentLabels as $status => $label)
                        @if (($paymentBreakdown[$status] ?? 0) > 0)
                            <li class="flex items-center justify-between px-5 py-3 text-sm">
                                <span class="text-admin-muted">{{ $label }}</span>
                                <span class="font-semibold tabular-nums">{{ number_format((int) $paymentBreakdown[$status]) }}</span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts.admin>
