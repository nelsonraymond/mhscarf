<x-layouts.admin>
    <x-slot:title>Kelola Produk - MH Scarf Admin</x-slot:title>
    <x-slot:header>Katalog Produk</x-slot:header>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="font-serif-display text-2xl font-bold text-[#3A3033]">Daftar Produk</h1>
            <p class="text-xs text-[#75686D] mt-0.5">Kelola katalog hijab, harga, varian warna, dan stok</p>
        </div>
        <a href="{{ route('admin.products.create') }}"
            class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl font-medium text-white bg-[#D98FAF] hover:bg-[#B97897] shadow-xs transition text-sm cursor-pointer">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Produk
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-[#EBDDE2] mb-6 shadow-xs">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama produk atau SKU..."
                    class="w-full pl-9 pr-4 py-2 rounded-xl border border-[#EBDDE2] text-sm text-[#3A3033] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-[#FFF9F5]/40">
                <svg class="w-4 h-4 text-[#75686D] absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div>
                <select name="category_id" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-xl border border-[#EBDDE2] text-sm text-[#3A3033] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-white">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <select name="status" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-xl border border-[#EBDDE2] text-sm text-[#3A3033] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-white">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                @if(request()->hasAny(['search', 'category_id', 'status']))
                    <a href="{{ route('admin.products.index') }}"
                        class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-[#75686D] rounded-xl text-sm transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-[#EBDDE2] overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-[#FFF9F5] border-b border-[#EBDDE2] text-xs font-semibold text-[#75686D] uppercase tracking-wider">
                        <th class="py-3.5 px-4">Produk</th>
                        <th class="py-3.5 px-4">SKU</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4">Harga Dasar</th>
                        <th class="py-3.5 px-4 text-center">Varian & Stok</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EBDDE2]">
                    @forelse ($products as $prod)
                        <tr class="hover:bg-[#FFF9F5]/40 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    @php $primaryImg = $prod->primaryImage(); @endphp
                                    @if ($primaryImg)
                                        <img src="{{ Storage::url($primaryImg->image_path) }}" alt="{{ $prod->name }}" class="w-10 h-12 object-cover rounded-lg border border-[#EBDDE2] shrink-0">
                                    @else
                                        <div class="w-10 h-12 rounded-lg bg-pink-50 border border-[#EBDDE2] flex items-center justify-center text-[#D98FAF] shrink-0 text-xs">
                                            Hijab
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.products.edit', $prod) }}" class="font-medium text-[#3A3033] hover:text-[#D98FAF] line-clamp-1">
                                            {{ $prod->name }}
                                        </a>
                                        <div class="flex items-center space-x-1.5 mt-0.5">
                                            @if($prod->is_featured)
                                                <span class="text-[10px] bg-amber-50 text-amber-700 px-1.5 py-0.2 rounded font-medium">Featured</span>
                                            @endif
                                            @if($prod->is_best_seller)
                                                <span class="text-[10px] bg-rose-50 text-rose-700 px-1.5 py-0.2 rounded font-medium">Best Seller</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs text-[#75686D]">
                                {{ $prod->sku }}
                            </td>
                            <td class="py-3.5 px-4 text-[#75686D]">
                                {{ $prod->category->name }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-[#3A3033]">
                                Rp{{ number_format($prod->base_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="text-xs font-semibold text-[#3A3033]">
                                    {{ $prod->totalStock() }} pcs
                                </div>
                                <div class="text-[10px] text-[#75686D]">
                                    {{ $prod->variants_count }} varian
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($prod->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <a href="{{ route('admin.products.edit', $prod) }}"
                                    class="text-xs font-medium text-[#D98FAF] hover:text-[#B97897] p-1">
                                    Kelola
                                </a>
                                <form method="POST" action="{{ route('admin.products.destroy', $prod) }}" class="inline-block"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-xs font-medium text-red-600 hover:text-red-700 p-1 cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-sm text-[#75686D]">
                                Belum ada produk yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="p-4 border-t border-[#EBDDE2]">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
