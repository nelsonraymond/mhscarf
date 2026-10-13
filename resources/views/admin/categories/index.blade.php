<x-layouts.admin>
    <x-slot:title>Kelola Kategori - MH Scarf Admin</x-slot:title>
    <x-slot:header>Kategori Produk</x-slot:header>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="font-serif-display text-2xl font-bold text-[#3A3033]">Daftar Kategori</h1>
            <p class="text-xs text-[#75686D] mt-0.5">Kelola kategori dan sub-kategori produk hijab</p>
        </div>
        <a href="{{ route('admin.categories.create') }}"
            class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl font-medium text-white bg-[#D98FAF] hover:bg-[#B97897] shadow-xs transition text-sm cursor-pointer">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kategori
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-[#EBDDE2] mb-6 shadow-xs">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama kategori..."
                    class="w-full pl-9 pr-4 py-2 rounded-xl border border-[#EBDDE2] text-sm text-[#3A3033] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-[#FFF9F5]/40">
                <svg class="w-4 h-4 text-[#75686D] absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="w-full sm:w-48">
                <select name="status" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-xl border border-[#EBDDE2] text-sm text-[#3A3033] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-white">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <button type="submit"
                class="px-4 py-2 bg-[#3A3033] hover:bg-[#524449] text-white rounded-xl text-sm font-medium transition cursor-pointer">
                Filter
            </button>

            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.categories.index') }}"
                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-[#75686D] rounded-xl text-sm transition text-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-2xl border border-[#EBDDE2] overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-[#FFF9F5] border-b border-[#EBDDE2] text-xs font-semibold text-[#75686D] uppercase tracking-wider">
                        <th class="py-3.5 px-4">Nama Kategori</th>
                        <th class="py-3.5 px-4">Slug</th>
                        <th class="py-3.5 px-4">Kategori Induk</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Produk</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EBDDE2]">
                    @forelse ($categories as $cat)
                        <tr class="hover:bg-[#FFF9F5]/40 transition">
                            <td class="py-3.5 px-4 font-medium text-[#3A3033]">
                                {{ $cat->name }}
                            </td>
                            <td class="py-3.5 px-4 text-[#75686D] font-mono text-xs">
                                {{ $cat->slug }}
                            </td>
                            <td class="py-3.5 px-4 text-[#75686D]">
                                {{ $cat->parent?->name ?? '—' }}
                            </td>
                            <td class="py-3.5 px-4 text-center text-[#75686D]">
                                {{ $cat->products_count }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($cat->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <a href="{{ route('admin.categories.edit', $cat) }}"
                                    class="text-xs font-medium text-[#D98FAF] hover:text-[#B97897] p-1">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="inline-block"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
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
                            <td colspan="6" class="py-8 text-center text-sm text-[#75686D]">
                                Belum ada kategori yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="p-4 border-t border-[#EBDDE2]">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
