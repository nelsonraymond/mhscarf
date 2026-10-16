<x-layouts.admin>
    <x-slot:title>Voucher - MH Scarf Admin</x-slot:title>
    <x-slot:header>Manajemen Voucher</x-slot:header>

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-2xl font-semibold tracking-tight text-admin-text">Voucher</h1>
            <p class="mt-1 text-sm text-admin-muted">Kode diskon yang bisa dipakai pembeli saat checkout.</p>
        </div>
        <a href="{{ route('admin.vouchers.create') }}" class="admin-btn admin-btn-primary">+ Buat voucher</a>
    </div>

    <form method="GET" action="{{ route('admin.vouchers.index') }}" class="admin-card mb-6 grid grid-cols-1 gap-3 p-4 sm:grid-cols-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode voucher..."
            class="sm:col-span-2 w-full rounded-lg border border-admin-border bg-admin-surface px-3 py-2 text-sm text-admin-text focus:outline-none focus:ring-2 focus:ring-admin-accent">
        <select name="status" onchange="this.form.submit()"
            class="w-full rounded-lg border border-admin-border bg-admin-surface px-3 py-2 text-sm text-admin-text focus:outline-none focus:ring-2 focus:ring-admin-accent">
            <option value="">Semua status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif sekarang</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Kedaluwarsa</option>
        </select>
    </form>

    <div class="admin-card overflow-hidden">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Potongan</th>
                        <th>Min. belanja</th>
                        <th>Pemakaian</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vouchers as $voucher)
                        <tr>
                            <td class="font-mono text-xs font-semibold text-admin-accent whitespace-nowrap">{{ $voucher->code }}</td>
                            <td>
                                <p class="font-medium text-admin-text">{{ $voucher->valueLabel() }}</p>
                                <p class="text-xs text-admin-muted">{{ $voucher->typeLabel() }}
                                    @if ($voucher->type === 'percentage' && $voucher->max_discount_amount)
                                        · maks. Rp{{ number_format($voucher->max_discount_amount, 0, ',', '.') }}
                                    @endif
                                </p>
                            </td>
                            <td class="whitespace-nowrap text-sm">
                                {{ $voucher->min_order_amount ? 'Rp'.number_format($voucher->min_order_amount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="whitespace-nowrap text-sm tabular-nums">
                                {{ $voucher->used_count }} / {{ $voucher->usage_limit ?? '∞' }}
                                @if ($voucher->usage_limit_per_user)
                                    <p class="text-xs text-admin-muted">maks. {{ $voucher->usage_limit_per_user }}x per pengguna</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-xs text-admin-muted">
                                {{ $voucher->starts_at ? $voucher->starts_at->format('d M Y H:i') : 'Langsung' }}<br>
                                s.d. {{ $voucher->expires_at ? $voucher->expires_at->format('d M Y H:i') : 'Tanpa batas' }}
                            </td>
                            <td><span class="admin-badge {{ $voucher->statusBadgeClass() }}">{{ $voucher->statusLabel() }}</span></td>
                            <td class="text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="text-xs font-medium text-admin-accent hover:underline">Ubah</a>

                                    <form method="POST" action="{{ route('admin.vouchers.toggle', $voucher) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs font-medium text-admin-muted hover:text-admin-text cursor-pointer">
                                            {{ $voucher->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    @if ($voucher->usages_count === 0)
                                        <form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}"
                                            data-confirm="Voucher {{ $voucher->code }} akan dihapus permanen."
                                            data-confirm-title="Hapus voucher?"
                                            data-confirm-ok="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-700 cursor-pointer">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-admin-muted">Belum ada voucher. Klik "Buat voucher" untuk membuat yang pertama.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vouchers->hasPages())
            <div class="border-t border-admin-border p-4">{{ $vouchers->links() }}</div>
        @endif
    </div>
</x-layouts.admin>