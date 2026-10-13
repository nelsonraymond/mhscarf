<x-layouts.admin>
    <x-slot:title>Tambah Kategori - MH Scarf Admin</x-slot:title>
    <x-slot:header>Kategori / Tambah Baru</x-slot:header>

    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="font-serif-display text-2xl font-bold text-[#3A3033]">Tambah Kategori Baru</h1>
            <a href="{{ route('admin.categories.index') }}" class="text-xs text-[#75686D] hover:text-[#3A3033]">
                ← Kembali ke Daftar
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-[#EBDDE2] p-6 sm:p-8 shadow-xs">
            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-[#3A3033] mb-1">
                        Nama Kategori <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40"
                        placeholder="Contoh: Pashmina Silk">
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-[#3A3033] mb-1">
                        Slug (URL)
                    </label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40"
                        placeholder="Biarkan kosong untuk generate otomatis">
                    <p class="text-xs text-[#75686D] mt-1">Kosongkan jika ingin dibuat otomatis dari nama kategori.</p>
                </div>

                <div>
                    <label for="parent_id" class="block text-sm font-medium text-[#3A3033] mb-1">
                        Kategori Induk (Opsional)
                    </label>
                    <select name="parent_id" id="parent_id"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-white">
                        <option value="">— Tidak Ada (Kategori Utama) —</option>
                        @foreach ($parentCategories as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-[#3A3033] mb-1">
                        Deskripsi
                    </label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] text-sm text-[#3A3033] bg-[#FFF9F5]/40"
                        placeholder="Deskripsi singkat kategori produk...">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center cursor-pointer text-sm text-[#3A3033]">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                            class="rounded border-[#EBDDE2] text-[#D98FAF] focus:ring-[#D98FAF] mr-2.5">
                        Aktifkan kategori ini di etalase toko
                    </label>
                </div>

                <div class="pt-4 border-t border-[#EBDDE2] flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.categories.index') }}"
                        class="px-4 py-2.5 rounded-xl border border-[#EBDDE2] text-sm text-[#75686D] hover:bg-[#FFF9F5] transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-[#D98FAF] hover:bg-[#B97897] text-white text-sm font-medium transition shadow-xs cursor-pointer">
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
