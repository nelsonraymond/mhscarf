<x-layouts.admin>
    <x-slot:title>Tambah Produk - MH Scarf Admin</x-slot:title>
    <x-slot:header>Produk / Tambah Baru</x-slot:header>

    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="font-serif-display text-2xl font-bold text-[#3A3033]">Tambah Produk Baru</h1>
            <a href="{{ route('admin.products.index') }}" class="text-xs text-[#75686D] hover:text-[#3A3033]">
                ← Kembali ke Daftar
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-[#EBDDE2] p-6 sm:p-8 shadow-xs">
            <form method="POST" action="{{ route('admin.products.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-[#3A3033] mb-1">
                            Nama Produk <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40"
                            placeholder="Contoh: Pashmina Silk Premium">
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-[#3A3033] mb-1">
                            Kategori <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id" id="category_id" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-white">
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="sku" class="block text-sm font-medium text-[#3A3033] mb-1">
                            SKU Produk <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40 font-mono"
                            placeholder="Contoh: PSK-001">
                    </div>

                    <div>
                        <label for="base_price" class="block text-sm font-medium text-[#3A3033] mb-1">
                            Harga Dasar (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="base_price" id="base_price" value="{{ old('base_price') }}" required min="0"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40"
                            placeholder="89000">
                    </div>

                    <div>
                        <label for="compare_at_price" class="block text-sm font-medium text-[#3A3033] mb-1">
                            Harga Coret / Diskon Awal (Rp)
                        </label>
                        <input type="number" step="0.01" name="compare_at_price" id="compare_at_price" value="{{ old('compare_at_price') }}" min="0"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40"
                            placeholder="109000 (opsional)">
                    </div>

                    <div>
                        <label for="material" class="block text-sm font-medium text-[#3A3033] mb-1">
                            Bahan / Material
                        </label>
                        <input type="text" name="material" id="material" value="{{ old('material') }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40"
                            placeholder="Contoh: Silk Satin Grade A">
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-[#3A3033] mb-1">
                            Slug URL (Opsional)
                        </label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40"
                            placeholder="Biarkan kosong untuk otomatis">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="care_instructions" class="block text-sm font-medium text-[#3A3033] mb-1">
                            Instruksi Perawatan
                        </label>
                        <input type="text" name="care_instructions" id="care_instructions" value="{{ old('care_instructions') }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40"
                            placeholder="Contoh: Cuci lembut dengan tangan, jangan peras terlalu kencang.">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="block text-sm font-medium text-[#3A3033] mb-1">
                            Deskripsi Produk
                        </label>
                        <textarea name="description" id="description" rows="4"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40"
                            placeholder="Ceritakan keunggulan, kenyamanan, dan tekstur hijab ini...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="flex flex-wrap gap-6 pt-2">
                    <label class="flex items-center cursor-pointer text-sm text-[#3A3033]">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                            class="rounded border-[#EBDDE2] text-[#D98FAF] focus:ring-[#D98FAF] mr-2">
                        Aktifkan di etalase toko
                    </label>

                    <label class="flex items-center cursor-pointer text-sm text-[#3A3033]">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                            class="rounded border-[#EBDDE2] text-[#D98FAF] focus:ring-[#D98FAF] mr-2">
                        Tandai sebagai Produk Pilihan (Featured)
                    </label>

                    <label class="flex items-center cursor-pointer text-sm text-[#3A3033]">
                        <input type="checkbox" name="is_best_seller" value="1" {{ old('is_best_seller') ? 'checked' : '' }}
                            class="rounded border-[#EBDDE2] text-[#D98FAF] focus:ring-[#D98FAF] mr-2">
                        Tandai sebagai Best Seller
                    </label>
                </div>

                <div class="pt-4 border-t border-[#EBDDE2] flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.products.index') }}"
                        class="px-4 py-2.5 rounded-xl border border-[#EBDDE2] text-sm text-[#75686D] hover:bg-[#FFF9F5] transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-[#D98FAF] hover:bg-[#B97897] text-white text-sm font-medium transition shadow-xs cursor-pointer">
                        Lanjut ke Varian & Gambar →
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
