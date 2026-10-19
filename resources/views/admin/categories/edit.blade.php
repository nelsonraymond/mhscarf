<x-layouts.admin>
    <x-slot:title>Edit Kategori - MH Scarf Admin</x-slot:title>
    <x-slot:header>Kategori / Edit {{ $category->name }}</x-slot:header>

    <div class="mx-auto max-w-2xl">
        <x-admin.page-header title="Edit Kategori" description="Perbarui nama, slug, dan visibilitas kategori.">
            <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn-secondary">Kembali</a>
        </x-admin.page-header>

        <div class="admin-card p-6 sm:p-8">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="admin-label">Nama kategori <span class="text-admin-danger">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
                        class="admin-input @error('name') admin-input-error @enderror">
                    @error('name')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="slug" class="admin-label">Slug (URL)</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug) }}" required
                        class="admin-input @error('slug') admin-input-error @enderror">
                    @error('slug')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="parent_id" class="admin-label">Kategori induk</label>
                    <select name="parent_id" id="parent_id" class="admin-input @error('parent_id') admin-input-error @enderror">
                        <option value="">— Tidak ada (kategori utama) —</option>
                        @foreach ($parentCategories as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="admin-label">Deskripsi</label>
                    <textarea name="description" id="description" rows="3"
                        class="admin-input @error('description') admin-input-error @enderror">{{ old('description', $category->description) }}</textarea>
                    @error('description')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="admin-label">Gambar Kategori</label>
                    <div class="mt-1 space-y-3">
                        @if ($category->image)
                            <div id="current-image-box" class="flex items-center gap-4 rounded-lg border border-admin-border bg-admin-bg/50 p-3">
                                <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="h-16 w-16 shrink-0 rounded-md border border-admin-border object-cover">
                                <div class="flex-1 text-xs">
                                    <p class="font-medium text-admin-text">Gambar saat ini</p>
                                    <p class="text-admin-muted">Unggah file baru di bawah untuk mengganti gambar ini.</p>
                                    <label class="mt-2 inline-flex items-center gap-1.5 font-medium text-admin-danger cursor-pointer hover:underline">
                                        <input type="checkbox" name="remove_image" value="1" id="remove-image-checkbox" class="rounded border-admin-border text-admin-danger focus:ring-admin-danger">
                                        Hapus gambar saat ini
                                    </label>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-4">
                            <div id="image-preview-container" class="relative hidden h-20 w-20 shrink-0 overflow-hidden rounded-lg border border-admin-border bg-admin-bg">
                                <img id="image-preview" src="" alt="Preview gambar baru" class="h-full w-full object-cover">
                                <button type="button" id="clear-image-btn" class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black" title="Batalkan pilihan">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="flex-1">
                                <label for="category-image-input" class="block text-xs text-admin-muted mb-1">{{ $category->image ? 'Pilih gambar baru (opsional):' : 'Pilih file gambar (opsional):' }}</label>
                                <input type="file" name="image" id="category-image-input" accept="image/jpeg,image/png,image/webp"
                                    class="w-full text-sm text-admin-muted file:mr-3 file:rounded-md file:border-0 file:bg-admin-accent file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-white hover:file:opacity-90 cursor-pointer">
                                <p class="mt-1 text-xs text-admin-muted">Format: JPG, PNG, WEBP. Maksimal 5MB. Ditampilkan di etalase kategori.</p>
                            </div>
                        </div>
                    </div>
                    @error('image')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-center text-sm text-admin-text">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                        class="mr-2.5 rounded border-admin-border text-admin-accent focus:ring-admin-accent">
                    Aktifkan kategori ini di etalase toko
                </label>

                <div class="flex items-center justify-end gap-3 border-t border-admin-border pt-4">
                    <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn-secondary">Batal</a>
                    <button type="submit" class="admin-btn admin-btn-primary">Perbarui Kategori</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('category-image-input');
            const container = document.getElementById('image-preview-container');
            const preview = document.getElementById('image-preview');
            const clearBtn = document.getElementById('clear-image-btn');
            const removeCheckbox = document.getElementById('remove-image-checkbox');
            const currentBox = document.getElementById('current-image-box');

            if (input && preview && container) {
                input.addEventListener('change', function () {
                    const file = this.files && this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            preview.src = e.target.result;
                            container.classList.remove('hidden');
                        };
                        reader.readAsDataURL(file);
                        if (removeCheckbox) {
                            removeCheckbox.checked = false;
                        }
                    } else {
                        preview.src = '';
                        container.classList.add('hidden');
                    }
                });

                if (clearBtn) {
                    clearBtn.addEventListener('click', function () {
                        input.value = '';
                        preview.src = '';
                        container.classList.add('hidden');
                    });
                }
            }

            if (removeCheckbox && currentBox) {
                removeCheckbox.addEventListener('change', function () {
                    if (this.checked) {
                        currentBox.classList.add('opacity-40');
                        if (input) {
                            input.value = '';
                        }
                        if (preview) {
                            preview.src = '';
                        }
                        if (container) {
                            container.classList.add('hidden');
                        }
                    } else {
                        currentBox.classList.remove('opacity-40');
                    }
                });
            }
        });
    </script>
</x-layouts.admin>
