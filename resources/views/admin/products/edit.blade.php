<x-layouts.admin>
    <x-slot:title>Edit Produk: {{ $product->name }} - MH Scarf Admin</x-slot:title>
    <x-slot:header>Produk / Kelola {{ $product->name }}</x-slot:header>

    <div class="space-y-8">
        <x-admin.page-header title="{{ $product->name }}" description="SKU: {{ $product->sku }} · Kategori: {{ $product->category->name }}">
            <span class="admin-badge {{ $product->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-slate-200 bg-slate-50 text-slate-600' }}">
                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
            <a href="{{ route('shop.product', $product->slug) }}" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-secondary">Preview di toko</a>
            <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-secondary">Kembali</a>
        </x-admin.page-header>

        <div class="admin-card p-6">
            <h2 class="mb-4 text-sm font-semibold text-admin-text">Informasi dasar produk</h2>
            <form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="admin-label">Nama produk</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                            class="admin-input @error('name') admin-input-error @enderror">
                        @error('name')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="category_id" class="admin-label">Kategori</label>
                        <select name="category_id" id="category_id" required class="admin-input @error('category_id') admin-input-error @enderror">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="sku" class="admin-label">SKU induk</label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" required
                            class="admin-input font-mono @error('sku') admin-input-error @enderror">
                        @error('sku')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="base_price" class="admin-label">Harga dasar (Rp)</label>
                        <input type="number" step="0.01" name="base_price" id="base_price" value="{{ old('base_price', $product->base_price) }}" required min="0"
                            class="admin-input @error('base_price') admin-input-error @enderror">
                        @error('base_price')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="compare_at_price" class="admin-label">Harga coret (Rp)</label>
                        <input type="number" step="0.01" name="compare_at_price" id="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}" min="0"
                            class="admin-input @error('compare_at_price') admin-input-error @enderror">
                        @error('compare_at_price')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="material" class="admin-label">Material / bahan</label>
                        <input type="text" name="material" id="material" value="{{ old('material', $product->material) }}"
                            class="admin-input @error('material') admin-input-error @enderror">
                        @error('material')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="slug" class="admin-label">Slug URL</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}" required
                            class="admin-input @error('slug') admin-input-error @enderror">
                        @error('slug')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="care_instructions" class="admin-label">Instruksi perawatan</label>
                        <input type="text" name="care_instructions" id="care_instructions" value="{{ old('care_instructions', $product->care_instructions) }}"
                            class="admin-input @error('care_instructions') admin-input-error @enderror">
                        @error('care_instructions')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="description" class="admin-label">Deskripsi produk</label>
                        <textarea name="description" id="description" rows="3"
                            class="admin-input @error('description') admin-input-error @enderror">{{ old('description', $product->description) }}</textarea>
                        @error('description')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center text-sm text-admin-text">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                            class="mr-2 rounded border-admin-border text-admin-accent focus:ring-admin-accent">
                        Aktifkan di toko
                    </label>
                    <label class="flex items-center text-sm text-admin-text">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                            class="mr-2 rounded border-admin-border text-admin-accent focus:ring-admin-accent">
                        Featured
                    </label>
                    <label class="flex items-center text-sm text-admin-text">
                        <input type="checkbox" name="is_best_seller" value="1" {{ old('is_best_seller', $product->is_best_seller) ? 'checked' : '' }}
                            class="mr-2 rounded border-admin-border text-admin-accent focus:ring-admin-accent">
                        Best seller
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="admin-btn admin-btn-dark">Perbarui informasi produk</button>
                </div>
            </form>
        </div>

        <div class="admin-card p-6">
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-admin-text">Varian produk &amp; manajemen stok</h2>
                <p class="text-xs text-admin-muted">Total stok tersedia: <strong class="text-admin-text">{{ $product->totalStock() }} pcs</strong></p>
            </div>

            <div class="mb-6 overflow-hidden rounded-lg border border-admin-border">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nama / warna</th>
                                <th>SKU varian</th>
                                <th>Ukuran</th>
                                <th>Harga tambahan</th>
                                <th class="text-center">Stok saat ini</th>
                                <th class="text-center">Penyesuaian stok</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($product->variants as $variant)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            @if ($variant->color_hex)
                                                <span class="h-4 w-4 shrink-0 rounded-full border border-admin-border" style="background-color: {{ $variant->color_hex }}"></span>
                                            @endif
                                            <span class="font-medium">{{ $variant->name }}</span>
                                        </div>
                                    </td>
                                    <td class="font-mono text-xs text-admin-muted">{{ $variant->sku }}</td>
                                    <td class="text-admin-muted">{{ $variant->size ?? 'All Size' }}</td>
                                    <td class="text-admin-muted">+Rp{{ number_format($variant->additional_price, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="admin-badge {{ $variant->stock_qty > $variant->low_stock_threshold ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
                                            {{ $variant->stock_qty }} pcs
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.products.variants.stock', [$product, $variant]) }}" class="flex flex-wrap items-center justify-center gap-1.5">
                                            @csrf
                                            <input type="hidden" name="expected_stock" value="{{ $variant->stock_qty }}">
                                            <label class="sr-only" for="stock-type-{{ $variant->id }}">Jenis penyesuaian</label>
                                            <select id="stock-type-{{ $variant->id }}" name="type" class="admin-input px-2 py-1 text-xs">
                                                <option value="in">+ Tambah</option>
                                                <option value="out">- Kurang</option>
                                                <option value="adjustment">= Set total</option>
                                            </select>
                                            <label class="sr-only" for="stock-qty-{{ $variant->id }}">Jumlah</label>
                                            <input id="stock-qty-{{ $variant->id }}" type="number" name="quantity" min="0" required placeholder="Qty"
                                                class="admin-input w-16 px-2 py-1 text-center text-xs">
                                            <button type="submit" class="admin-btn admin-btn-primary px-2 py-1 text-xs">Update</button>
                                        </form>
                                    </td>
                                    <td class="text-right">
                                        <form method="POST" action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}"
                                            data-confirm="Hapus varian ini?"
                                            data-confirm-title="Hapus varian"
                                            data-confirm-ok="Ya, hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-admin-danger hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-xs text-admin-muted">Belum ada varian. Silakan tambahkan varian pertama di bawah ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-lg border border-admin-border bg-admin-bg/60 p-5">
                <h3 class="mb-3 text-sm font-medium text-admin-text">Tambah varian baru</h3>
                <form method="POST" action="{{ route('admin.products.variants.store', $product) }}" class="grid grid-cols-1 gap-3 sm:grid-cols-6">
                    @csrf
                    <div class="sm:col-span-2">
                        <label for="variant-name" class="admin-label text-xs">Nama varian / warna *</label>
                        <input id="variant-name" type="text" name="name" required placeholder="Contoh: Soft Rose" class="admin-input text-xs">
                    </div>
                    <div>
                        <label for="variant-sku" class="admin-label text-xs">SKU varian *</label>
                        <input id="variant-sku" type="text" name="sku" required placeholder="SKU-VAR" class="admin-input font-mono text-xs">
                    </div>
                    <div>
                        <label for="variant-hex" class="admin-label text-xs">Kode hex</label>
                        <input id="variant-hex" type="text" name="color_hex" placeholder="#2563EB" class="admin-input text-xs">
                    </div>
                    <div>
                        <label for="variant-stock" class="admin-label text-xs">Stok awal</label>
                        <input id="variant-stock" type="number" name="stock_qty" min="0" value="10" class="admin-input text-xs">
                    </div>
                    <div>
                        <label for="variant-price" class="admin-label text-xs">Biaya tambahan (Rp)</label>
                        <input id="variant-price" type="number" step="0.01" name="additional_price" value="0" min="0" class="admin-input text-xs">
                    </div>
                    <div class="sm:col-span-6 flex justify-end">
                        <button type="submit" class="admin-btn admin-btn-primary text-xs">Tambah varian</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="admin-card p-6">
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-admin-text">Galeri gambar produk</h2>
                <p class="text-xs text-admin-muted">Upload gambar format JPG, PNG, WEBP (maks 5MB)</p>
            </div>

            <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4 md:grid-cols-6">
                @forelse ($product->images as $img)
                    <div class="overflow-hidden rounded-lg border border-admin-border bg-white">
                        <img src="{{ $img->image_url }}" alt="{{ $img->alt_text }}" class="h-36 w-full object-cover">
                        @if ($img->is_primary)
                            <span class="absolute sr-only">Gambar utama</span>
                        @endif
                        <div class="flex items-center justify-between gap-1 p-2">
                            @if (! $img->is_primary)
                                <form method="POST" action="{{ route('admin.products.images.primary', [$product, $img]) }}">
                                    @csrf
                                    <button type="submit" class="text-[10px] font-medium text-admin-accent hover:underline">Jadikan utama</button>
                                </form>
                            @else
                                <span class="text-[10px] text-admin-muted">Gambar utama</span>
                            @endif
                            <form method="POST" action="{{ route('admin.products.images.destroy', [$product, $img]) }}"
                                data-confirm="Hapus gambar ini?"
                                data-confirm-title="Hapus gambar"
                                data-confirm-ok="Ya, hapus">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[10px] font-medium text-admin-danger hover:underline">Hapus</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-lg border border-dashed border-admin-border py-6 text-center text-xs text-admin-muted">
                        Belum ada gambar yang diunggah untuk produk ini.
                    </div>
                @endforelse
            </div>

            <div class="rounded-lg border border-admin-border bg-admin-bg/60 p-5">
                <h3 class="mb-3 text-sm font-medium text-admin-text">Upload gambar baru</h3>
                <form method="POST" action="{{ route('admin.products.images.store', $product) }}" enctype="multipart/form-data"
                    class="grid grid-cols-1 items-end gap-3 sm:grid-cols-3">
                    @csrf
                    <div>
                        <label for="image" class="admin-label text-xs">File gambar *</label>
                        <input id="image" type="file" name="image" required accept="image/jpeg,image/png,image/webp"
                            class="w-full text-xs text-admin-muted file:mr-3 file:rounded-md file:border-0 file:bg-admin-accent file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-white">
                    </div>
                    <div>
                        <label for="variant_id" class="admin-label text-xs">Kaitkan dengan varian (opsional)</label>
                        <select id="variant_id" name="variant_id" class="admin-input text-xs">
                            <option value="">— Gambar umum produk —</option>
                            @foreach ($product->variants as $variant)
                                <option value="{{ $variant->id }}">{{ $variant->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center text-xs text-admin-text">
                            <input type="checkbox" name="is_primary" value="1" class="mr-1.5 rounded border-admin-border text-admin-accent focus:ring-admin-accent">
                            Jadikan utama
                        </label>
                        <button type="submit" class="admin-btn admin-btn-primary ml-auto text-xs">Upload gambar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
