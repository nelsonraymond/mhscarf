<x-layouts.admin>
    <x-slot:title>Kelola Produk - MH Scarf Admin</x-slot:title>
    <x-slot:header>Produk</x-slot:header>

    <x-admin.page-header title="Daftar Produk" description="Kelola katalog, harga, varian, dan stok. {{ $products->total() }} produk terdaftar.">
        <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn-primary">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Produk
        </a>
    </x-admin.page-header>

    <div class="admin-card mb-6 p-4">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-4">
            <div class="relative sm:col-span-2 admin-search-wrap">
                <label for="product-search" class="sr-only">Cari produk atau SKU</label>
                <input id="product-search" type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama produk atau SKU..."
                    class="admin-input pl-10">
                <svg class="admin-search-icon pointer-events-none absolute left-3.5 top-[calc(50%+2px)] -translate-y-1/2 h-4 w-4 text-admin-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <div>
                <label for="product-category" class="sr-only">Kategori</label>
                <select id="product-category" name="category_id" class="admin-input">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <label for="product-status" class="sr-only">Status</label>
                <select id="product-status" name="status" class="admin-input">
                    <option value="">Semua status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <button type="submit" class="admin-btn admin-btn-dark shrink-0">Filter</button>
                @if (request()->hasAny(['search', 'category_id', 'status']))
                    <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-secondary shrink-0">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="admin-card overflow-hidden">
        @if ($products->isEmpty())
            <x-admin.empty-state
                title="Belum ada produk yang ditemukan"
                description="Tambahkan produk pertama untuk mulai menjual di etalase."
                action-href="{{ route('admin.products.create') }}"
                action-label="Tambah produk"
            />
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>SKU</th>
                            <th>Kategori</th>
                            <th>Harga dasar</th>
                            <th class="text-center">Varian & stok</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $prod)
                            @php $primaryImg = $prod->primaryImage(); @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        @if ($primaryImg)
                                            <img src="{{ Storage::url($primaryImg->image_url) }}" alt="{{ $prod->name }}" class="h-12 w-10 shrink-0 rounded-md border border-admin-border object-cover">
                                        @else
                                            <div class="flex h-12 w-10 shrink-0 items-center justify-center rounded-md border border-admin-border bg-admin-bg text-[10px] text-admin-muted">No img</div>
                                        @endif
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.products.edit', $prod) }}" class="line-clamp-1 font-medium text-admin-text hover:text-admin-accent">
                                                {{ $prod->name }}
                                            </a>
                                            <div class="mt-0.5 flex flex-wrap gap-1">
                                                @if ($prod->is_featured)
                                                    <span class="admin-badge border-amber-200 bg-amber-50 text-amber-800">Featured</span>
                                                @endif
                                                @if ($prod->is_best_seller)
                                                    <span class="admin-badge border-slate-200 bg-slate-50 text-slate-700">Best seller</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-mono text-xs text-admin-muted">{{ $prod->sku }}</td>
                                <td class="text-admin-muted">{{ $prod->category->name }}</td>
                                <td class="font-medium">Rp{{ number_format($prod->base_price, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <div class="text-xs font-semibold text-admin-text">{{ $prod->totalStock() }} pcs</div>
                                    <div class="text-[11px] text-admin-muted">{{ $prod->variants_count }} varian</div>
                                </td>
                                <td class="text-center">
                                    @if ($prod->is_active)
                                        <span class="admin-badge border-emerald-200 bg-emerald-50 text-emerald-800">Aktif</span>
                                    @else
                                        <span class="admin-badge border-slate-200 bg-slate-50 text-slate-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.products.edit', $prod) }}" class="text-xs font-medium text-admin-accent hover:underline">Kelola</a>
                                        <form method="POST" action="{{ route('admin.products.destroy', $prod) }}"
                                            data-confirm="Apakah Anda yakin ingin menghapus produk ini?"
                                            data-confirm-title="Hapus produk"
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
            @if ($products->hasPages())
                <div class="border-t border-admin-border p-4">
                    {{ $products->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.admin>
