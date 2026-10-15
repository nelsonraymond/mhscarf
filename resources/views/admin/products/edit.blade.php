<x-layouts.admin>
    <x-slot:title>Edit Produk: {{ $product->name }} - MH Scarf Admin</x-slot:title>
    <x-slot:header>Produk / Kelola {{ $product->name }}</x-slot:header>

    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="font-serif-display text-2xl font-bold text-[#3A3033]">{{ $product->name }}</h1>
                    <span class="text-xs px-2.5 py-0.5 rounded-full {{ $product->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }} font-medium">
                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <p class="text-xs text-[#75686D] mt-0.5">SKU: {{ $product->sku }} | Kategori: {{ $product->category->name }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('shop.product', $product->slug) }}" target="_blank"
                    class="px-3.5 py-2 rounded-xl border border-[#EBDDE2] text-xs font-medium text-[#75686D] hover:bg-[#FFF9F5] transition">
                    Preview di Toko ↗
                </a>
                <a href="{{ route('admin.products.index') }}" class="text-xs text-[#75686D] hover:text-[#3A3033]">
                    ← Kembali
                </a>
            </div>
        </div>

        <!-- Section 1: Informasi Produk -->
        <div class="bg-white rounded-2xl border border-[#EBDDE2] p-6 shadow-xs">
            <h2 class="font-serif-display text-lg font-semibold text-[#3A3033] mb-4">Informasi Dasar Produk</h2>
            <form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-[#3A3033] mb-1">Nama Produk</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40">
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-[#3A3033] mb-1">Kategori</label>
                        <select name="category_id" id="category_id" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-white">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="sku" class="block text-sm font-medium text-[#3A3033] mb-1">SKU Induk</label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40 font-mono">
                    </div>

                    <div>
                        <label for="base_price" class="block text-sm font-medium text-[#3A3033] mb-1">Harga Dasar (Rp)</label>
                        <input type="number" step="0.01" name="base_price" id="base_price" value="{{ old('base_price', $product->base_price) }}" required min="0"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40">
                    </div>

                    <div>
                        <label for="compare_at_price" class="block text-sm font-medium text-[#3A3033] mb-1">Harga Coret (Rp)</label>
                        <input type="number" step="0.01" name="compare_at_price" id="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}" min="0"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40">
                    </div>

                    <div>
                        <label for="material" class="block text-sm font-medium text-[#3A3033] mb-1">Material / Bahan</label>
                        <input type="text" name="material" id="material" value="{{ old('material', $product->material) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40">
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-[#3A3033] mb-1">Slug URL</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="care_instructions" class="block text-sm font-medium text-[#3A3033] mb-1">Instruksi Perawatan</label>
                        <input type="text" name="care_instructions" id="care_instructions" value="{{ old('care_instructions', $product->care_instructions) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="block text-sm font-medium text-[#3A3033] mb-1">Deskripsi Produk</label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>

                <div class="flex flex-wrap gap-6 pt-2">
                    <label class="flex items-center cursor-pointer text-sm text-[#3A3033]">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                            class="rounded border-[#EBDDE2] text-[#D98FAF] focus:ring-[#D98FAF] mr-2">
                        Aktifkan di toko
                    </label>

                    <label class="flex items-center cursor-pointer text-sm text-[#3A3033]">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                            class="rounded border-[#EBDDE2] text-[#D98FAF] focus:ring-[#D98FAF] mr-2">
                        Featured
                    </label>

                    <label class="flex items-center cursor-pointer text-sm text-[#3A3033]">
                        <input type="checkbox" name="is_best_seller" value="1" {{ old('is_best_seller', $product->is_best_seller) ? 'checked' : '' }}
                            class="rounded border-[#EBDDE2] text-[#D98FAF] focus:ring-[#D98FAF] mr-2">
                        Best Seller
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-[#3A3033] hover:bg-[#524449] text-white text-sm font-medium transition cursor-pointer">
                        Perbarui Informasi Produk
                    </button>
                </div>
            </form>
        </div>

        <!-- Section 2: Varian Produk & Stok -->
        <div class="bg-white rounded-2xl border border-[#EBDDE2] p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-serif-display text-lg font-semibold text-[#3A3033]">Varian Produk & Manajemen Stok</h2>
                    <p class="text-xs text-[#75686D]">Total Stok Tersedia: <strong class="text-[#3A3033]">{{ $product->totalStock() }} pcs</strong></p>
                </div>
            </div>

            <!-- Existing Variants Table -->
            <div class="overflow-x-auto border border-[#EBDDE2] rounded-xl mb-6">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#FFF9F5] text-xs font-semibold text-[#75686D] uppercase border-b border-[#EBDDE2]">
                        <tr>
                            <th class="py-3 px-3.5">Nama / Warna</th>
                            <th class="py-3 px-3.5">SKU Varian</th>
                            <th class="py-3 px-3.5">Ukuran</th>
                            <th class="py-3 px-3.5">Harga Tambahan</th>
                            <th class="py-3 px-3.5 text-center">Stok Saat Ini</th>
                            <th class="py-3 px-3.5 text-center">Penyesuaian Stok</th>
                            <th class="py-3 px-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDDE2]">
                        @forelse($product->variants as $variant)
                            <tr class="hover:bg-[#FFF9F5]/40 transition">
                                <td class="py-3 px-3.5">
                                    <div class="flex items-center space-x-2">
                                        @if($variant->color_hex)
                                            <span class="w-4 h-4 rounded-full border border-gray-300 shrink-0" style="background-color: {{ $variant->color_hex }}"></span>
                                        @endif
                                        <span class="font-medium text-[#3A3033]">{{ $variant->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-3.5 font-mono text-xs text-[#75686D]">{{ $variant->sku }}</td>
                                <td class="py-3 px-3.5 text-[#75686D]">{{ $variant->size ?? 'All Size' }}</td>
                                <td class="py-3 px-3.5 text-[#75686D]">
                                    +Rp{{ number_format($variant->additional_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $variant->stock_qty > $variant->low_stock_threshold ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ $variant->stock_qty }} pcs
                                    </span>
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    <!-- Stock adjustment form -->
                                    <form method="POST" action="{{ route('admin.products.variants.stock', [$product, $variant]) }}" class="inline-flex items-center gap-1.5">
    @csrf
    <input type="hidden" name="expected_stock" value="{{ $variant->stock_qty }}">
    <select name="type" class="text-xs px-2 py-1 rounded-lg border border-[#EBDDE2] bg-white">
                                            <option value="in">+ Tambah</option>
                                            <option value="out">- Kurang</option>
                                            <option value="adjustment">= Set Total</option>
                                        </select>
                                        <input type="number" name="quantity" min="0" required placeholder="Qty"
                                            class="w-16 px-2 py-1 text-xs rounded-lg border border-[#EBDDE2] bg-[#FFF9F5]/40 text-center">
                                        <button type="submit" class="px-2 py-1 bg-[#D98FAF] hover:bg-[#B97897] text-white rounded-lg text-xs font-medium cursor-pointer">
                                            Update
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3 px-3.5 text-right">
                                    <form method="POST" action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}" class="inline-block"
                                        onsubmit="return confirm('Hapus varian ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:text-red-700 cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-xs text-[#75686D]">
                                    Belum ada varian. Silakan tambahkan varian pertama di bawah ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Form Tambah Varian Baru -->
            <div class="bg-[#FFF9F5]/50 border border-[#EBDDE2] rounded-xl p-5">
                <h3 class="font-medium text-sm text-[#3A3033] mb-3">Tambah Varian Baru</h3>
                <form method="POST" action="{{ route('admin.products.variants.store', $product) }}" class="grid grid-cols-1 sm:grid-cols-6 gap-3">
                    @csrf
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-[#75686D] mb-1">Nama Varian / Warna *</label>
                        <input type="text" name="name" required placeholder="Contoh: Soft Rose"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-[#EBDDE2] bg-white">
                    </div>
                    <div>
                        <label class="block text-xs text-[#75686D] mb-1">SKU Varian *</label>
                        <input type="text" name="sku" required placeholder="SKU-VAR"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-[#EBDDE2] bg-white font-mono">
                    </div>
                    <div>
                        <label class="block text-xs text-[#75686D] mb-1">Kode Hex</label>
                        <input type="text" name="color_hex" placeholder="#D98FAF"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-[#EBDDE2] bg-white">
                    </div>
                    <div>
                        <label class="block text-xs text-[#75686D] mb-1">Stok Awal</label>
                        <input type="number" name="stock_qty" min="0" value="10"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-[#EBDDE2] bg-white">
                    </div>
                    <div>
                        <label class="block text-xs text-[#75686D] mb-1">Biaya Tambahan (Rp)</label>
                        <input type="number" step="0.01" name="additional_price" value="0" min="0"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-[#EBDDE2] bg-white">
                    </div>
                    <div class="sm:col-span-6 flex justify-end">
                        <button type="submit"
                            class="px-4 py-2 bg-[#D98FAF] hover:bg-[#B97897] text-white rounded-xl text-xs font-medium transition cursor-pointer">
                            + Tambah Varian
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Section 3: Gambar Produk -->
        <div class="bg-white rounded-2xl border border-[#EBDDE2] p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-serif-display text-lg font-semibold text-[#3A3033]">Galeri Gambar Produk</h2>
                    <p class="text-xs text-[#75686D]">Upload gambar format JPG, PNG, WEBP (maks 5MB)</p>
                </div>
            </div>

            <!-- Existing Images Gallery -->
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4 mb-6">
                @forelse($product->images as $img)
                    <div class="relative group rounded-xl border border-[#EBDDE2] overflow-hidden bg-white">
                        <img src="{{ Storage::url($img->image_path) }}" alt="{{ $img->alt_text }}" class="w-full h-36 object-cover">
                        @if($img->is_primary)
                            <span class="absolute top-2 left-2 bg-[#D98FAF] text-white text-[10px] font-semibold px-2 py-0.5 rounded-full shadow-xs">
                                Utama
                            </span>
                        @endif
                        <div class="p-2 bg-white flex items-center justify-between">
                            @if(! $img->is_primary)
                                <form method="POST" action="{{ route('admin.products.images.primary', [$product, $img]) }}">
                                    @csrf
                                    <button type="submit" class="text-[10px] text-[#D98FAF] hover:underline cursor-pointer">
                                        Jadikan Utama
                                    </button>
                                </form>
                            @else
                                <span class="text-[10px] text-gray-400">Gambar Utama</span>
                            @endif

                            <form method="POST" action="{{ route('admin.products.images.destroy', [$product, $img]) }}"
                                onsubmit="return confirm('Hapus gambar ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[10px] text-red-600 hover:underline cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-6 text-center text-xs text-[#75686D] border border-dashed border-[#EBDDE2] rounded-xl">
                        Belum ada gambar yang diunggah untuk produk ini.
                    </div>
                @endforelse
            </div>

            <!-- Upload Image Form -->
            <div class="bg-[#FFF9F5]/50 border border-[#EBDDE2] rounded-xl p-5">
                <h3 class="font-medium text-sm text-[#3A3033] mb-3">Upload Gambar Baru</h3>
                <form method="POST" action="{{ route('admin.products.images.store', $product) }}" enctype="multipart/form-data"
                    class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs text-[#75686D] mb-1">File Gambar *</label>
                        <input type="file" name="image" required accept="image/jpeg,image/png,image/webp"
                            class="w-full text-xs text-[#75686D] file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-medium file:bg-pink-100 file:text-[#D98FAF] hover:file:bg-pink-200 cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-xs text-[#75686D] mb-1">Kaitkan dengan Varian (Opsional)</label>
                        <select name="variant_id" class="w-full px-3 py-1.5 text-xs rounded-xl border border-[#EBDDE2] bg-white">
                            <option value="">— Gambar Umum Produk —</option>
                            @foreach($product->variants as $variant)
                                <option value="{{ $variant->id }}">{{ $variant->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="flex items-center text-xs text-[#3A3033] cursor-pointer">
                            <input type="checkbox" name="is_primary" value="1" class="rounded border-[#EBDDE2] text-[#D98FAF] focus:ring-[#D98FAF] mr-1.5">
                            Jadikan Utama
                        </label>
                        <button type="submit"
                            class="px-4 py-2 bg-[#D98FAF] hover:bg-[#B97897] text-white rounded-xl text-xs font-medium transition cursor-pointer ml-auto">
                            Upload Gambar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
