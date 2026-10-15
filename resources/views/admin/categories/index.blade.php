<x-layouts.admin>
    <x-slot:title>Kelola Kategori - MH Scarf Admin</x-slot:title>
    <x-slot:header>Kategori</x-slot:header>

    <x-admin.page-header title="Daftar Kategori" description="Kelola kategori dan sub-kategori produk. {{ $categories->total() }} kategori terdaftar.">
        <a href="{{ route('admin.categories.create') }}" class="admin-btn admin-btn-primary">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kategori
        </a>
    </x-admin.page-header>

    <div class="admin-card mb-6 p-4">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1 admin-search-wrap">
                <label for="category-search" class="sr-only">Cari nama kategori</label>
                <input id="category-search" type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama kategori..."
                    class="admin-input pl-10">
                <svg class="admin-search-icon pointer-events-none absolute left-3.5 top-[calc(50%+2px)] -translate-y-1/2 h-4 w-4 text-admin-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <div class="w-full sm:w-48">
                <label for="category-status" class="sr-only">Status</label>
                <select id="category-status" name="status" class="admin-input">
                    <option value="">Semua status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <button type="submit" class="admin-btn admin-btn-dark">Filter</button>
            @if (request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    <div class="admin-card overflow-hidden">
        @if ($categories->isEmpty())
            <x-admin.empty-state
                title="Belum ada kategori yang ditemukan"
                description="Tambahkan kategori agar produk dapat dikelompokkan di etalase."
                action-href="{{ route('admin.categories.create') }}"
                action-label="Tambah kategori"
            />
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama kategori</th>
                            <th>Slug</th>
                            <th>Kategori induk</th>
                            <th class="text-center">Jumlah produk</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $cat)
                            <tr>
                                <td class="font-medium text-admin-text">
                                    <div class="flex items-center gap-3">
                                        @if ($cat->image)
                                            <img src="{{ Storage::url($cat->image) }}" alt="{{ $cat->name }}" class="h-10 w-10 shrink-0 rounded-lg border border-admin-border object-cover">
                                        @else
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-dashed border-admin-border bg-admin-bg text-admin-muted">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                        <span>{{ $cat->name }}</span>
                                    </div>
                                </td>
                                <td class="font-mono text-xs text-admin-muted">{{ $cat->slug }}</td>
                                <td class="text-admin-muted">{{ $cat->parent?->name ?? '—' }}</td>
                                <td class="text-center text-admin-muted">{{ $cat->products_count }}</td>
                                <td class="text-center">
                                    @if ($cat->is_active)
                                        <span class="admin-badge border-emerald-200 bg-emerald-50 text-emerald-800">Aktif</span>
                                    @else
                                        <span class="admin-badge border-slate-200 bg-slate-50 text-slate-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.categories.edit', $cat) }}" class="text-xs font-medium text-admin-accent hover:underline">Edit</a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}"
                                            data-confirm="Apakah Anda yakin ingin menghapus kategori ini?"
                                            data-confirm-title="Hapus kategori"
                                            data-confirm-ok="Ya, hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-admin-danger hover:underline">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($categories->hasPages())
                <div class="border-t border-admin-border p-4">
                    {{ $categories->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.admin>
