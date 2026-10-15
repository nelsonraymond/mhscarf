<x-layouts.admin>
    <x-slot:title>Tambah Produk - MH Scarf Admin</x-slot:title>
    <x-slot:header>Produk / Tambah Baru</x-slot:header>

    <div class="mx-auto max-w-4xl">
        <x-admin.page-header title="Tambah Produk Baru" description="Isi informasi dasar. Varian dan gambar ditambahkan pada langkah berikutnya.">
            <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-secondary">Kembali</a>
        </x-admin.page-header>

        <div class="admin-card p-6 sm:p-8">
            <form method="POST" action="{{ route('admin.products.store') }}" class="space-y-6">
                @csrf

                <fieldset class="space-y-5">
                    <legend class="text-sm font-semibold text-admin-text">Informasi produk</legend>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="admin-label">Nama produk <span class="text-admin-danger">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="admin-input @error('name') admin-input-error @enderror"
                                placeholder="Contoh: Pashmina Silk Premium">
                            @error('name')<p class="admin-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="category_id" class="admin-label">Kategori <span class="text-admin-danger">*</span></label>
                            <select name="category_id" id="category_id" required class="admin-input @error('category_id') admin-input-error @enderror">
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<p class="admin-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="sku" class="admin-label">SKU produk <span class="text-admin-danger">*</span></label>
                            <input type="text" name="sku" id="sku" value="{{ old('sku') }}" required
                                class="admin-input font-mono @error('sku') admin-input-error @enderror"
                                placeholder="Contoh: PSK-001">
                            @error('sku')<p class="admin-field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="space-y-5">
                    <legend class="text-sm font-semibold text-admin-text">Harga</legend>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="base_price" class="admin-label">Harga dasar (Rp) <span class="text-admin-danger">*</span></label>
                            <input type="number" step="0.01" name="base_price" id="base_price" value="{{ old('base_price') }}" required min="0"
                                class="admin-input @error('base_price') admin-input-error @enderror"
                                placeholder="89000">
                            @error('base_price')<p class="admin-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="compare_at_price" class="admin-label">Harga coret / diskon awal (Rp)</label>
                            <input type="number" step="0.01" name="compare_at_price" id="compare_at_price" value="{{ old('compare_at_price') }}" min="0"
                                class="admin-input @error('compare_at_price') admin-input-error @enderror"
                                placeholder="109000 (opsional)">
                            @error('compare_at_price')<p class="admin-field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="space-y-5">
                    <legend class="text-sm font-semibold text-admin-text">Detail</legend>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="material" class="admin-label">Bahan / material</label>
                            <input type="text" name="material" id="material" value="{{ old('material') }}"
                                class="admin-input @error('material') admin-input-error @enderror"
                                placeholder="Contoh: Silk Satin Grade A">
                            @error('material')<p class="admin-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="slug" class="admin-label">Slug URL (opsional)</label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                                class="admin-input @error('slug') admin-input-error @enderror"
                                placeholder="Biarkan kosong untuk otomatis">
                            @error('slug')<p class="admin-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="care_instructions" class="admin-label">Instruksi perawatan</label>
                            <input type="text" name="care_instructions" id="care_instructions" value="{{ old('care_instructions') }}"
                                class="admin-input @error('care_instructions') admin-input-error @enderror"
                                placeholder="Contoh: Cuci lembut dengan tangan, jangan peras terlalu kencang.">
                            @error('care_instructions')<p class="admin-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="description" class="admin-label">Deskripsi produk</label>
                            <textarea name="description" id="description" rows="4"
                                class="admin-input @error('description') admin-input-error @enderror"
                                placeholder="Ceritakan keunggulan, kenyamanan, dan tekstur hijab ini...">{{ old('description') }}</textarea>
                            @error('description')<p class="admin-field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>

                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center text-sm text-admin-text">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                            class="mr-2 rounded border-admin-border text-admin-accent focus:ring-admin-accent">
                        Aktifkan di etalase toko
                    </label>
                    <label class="flex items-center text-sm text-admin-text">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                            class="mr-2 rounded border-admin-border text-admin-accent focus:ring-admin-accent">
                        Tandai sebagai produk pilihan (featured)
                    </label>
                    <label class="flex items-center text-sm text-admin-text">
                        <input type="checkbox" name="is_best_seller" value="1" {{ old('is_best_seller') ? 'checked' : '' }}
                            class="mr-2 rounded border-admin-border text-admin-accent focus:ring-admin-accent">
                        Tandai sebagai best seller
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-admin-border pt-4">
                    <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                    <button type="submit" class="admin-btn admin-btn-primary">Lanjut ke varian &amp; gambar</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
