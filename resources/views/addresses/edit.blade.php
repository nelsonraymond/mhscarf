<x-layouts.app>
    @section('title', 'Ubah Alamat Pengiriman - MH Scarf')

<div class="max-w-2xl w-full mx-auto px-4 sm:px-6 py-8 md:py-12">
    <div class="mb-6">
        <a href="{{ route('addresses.index') }}" class="text-muted hover:text-pink-deep text-xs font-medium transition">
            ← Kembali ke Buku Alamat
        </a>
        <p class="text-[11px] tracking-[0.25em] uppercase text-pink-deep mt-5 mb-2">Buku Alamat</p>
        <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink">Ubah Alamat Pengiriman</h1>
        <p class="text-xs sm:text-sm text-muted mt-1">Perbarui informasi penerima dan tujuan pengiriman.</p>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <p class="font-semibold text-rose-900">Periksa kesalahan pengisian form berikut:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-line p-6 sm:p-8 shadow-card">
        <form method="POST" action="{{ route('addresses.update', $address) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="label" class="block text-xs font-medium text-muted mb-1">Label Alamat (Opsional)</label>
                    <input type="text" id="label" name="label" value="{{ old('label', $address->label) }}" placeholder="Contoh: Rumah, Kantor"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-cream/40 text-sm focus:outline-none focus:border-pink-deep focus:ring-1 focus:ring-pink-deep">
                </div>

                <div>
                    <label for="recipient_name" class="block text-xs font-medium text-muted mb-1">Nama Penerima <span class="text-rose-500">*</span></label>
                    <input type="text" id="recipient_name" name="recipient_name" value="{{ old('recipient_name', $address->recipient_name) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-cream/40 text-sm focus:outline-none focus:border-pink-deep focus:ring-1 focus:ring-pink-deep">
                </div>
            </div>

            <div>
                <label for="phone" class="block text-xs font-medium text-muted mb-1">Nomor Telepon / WhatsApp <span class="text-rose-500">*</span></label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', $address->phone) }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-cream/40 text-sm focus:outline-none focus:border-pink-deep focus:ring-1 focus:ring-pink-deep">
            </div>

            <div>
                <label for="address_line" class="block text-xs font-medium text-muted mb-1">Alamat Lengkap <span class="text-rose-500">*</span></label>
                <textarea id="address_line" name="address_line" rows="3" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-cream/40 text-sm focus:outline-none focus:border-pink-deep focus:ring-1 focus:ring-pink-deep">{{ old('address_line', $address->address_line) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="district" class="block text-xs font-medium text-muted mb-1">Kecamatan</label>
                    <input type="text" id="district" name="district" value="{{ old('district', $address->district) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-cream/40 text-sm focus:outline-none focus:border-pink-deep focus:ring-1 focus:ring-pink-deep">
                </div>

                <div>
                    <label for="village" class="block text-xs font-medium text-muted mb-1">Kelurahan / Desa</label>
                    <input type="text" id="village" name="village" value="{{ old('village', $address->village) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-cream/40 text-sm focus:outline-none focus:border-pink-deep focus:ring-1 focus:ring-pink-deep">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="city" class="block text-xs font-medium text-muted mb-1">Kota / Kabupaten <span class="text-rose-500">*</span></label>
                    <input type="text" id="city" name="city" value="{{ old('city', $address->city) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-cream/40 text-sm focus:outline-none focus:border-pink-deep focus:ring-1 focus:ring-pink-deep">
                </div>

                <div>
                    <label for="province" class="block text-xs font-medium text-muted mb-1">Provinsi <span class="text-rose-500">*</span></label>
                    <input type="text" id="province" name="province" value="{{ old('province', $address->province) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-cream/40 text-sm focus:outline-none focus:border-pink-deep focus:ring-1 focus:ring-pink-deep">
                </div>

                <div>
                    <label for="postal_code" class="block text-xs font-medium text-muted mb-1">Kode Pos <span class="text-rose-500">*</span></label>
                    <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $address->postal_code) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-cream/40 text-sm focus:outline-none focus:border-pink-deep focus:ring-1 focus:ring-pink-deep">
                </div>
            </div>

            <div>
                <label for="notes" class="block text-xs font-medium text-muted mb-1">Catatan Tambahan / Patokan (Opsional)</label>
                <input type="text" id="notes" name="notes" value="{{ old('notes', $address->notes) }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-cream/40 text-sm focus:outline-none focus:border-pink-deep focus:ring-1 focus:ring-pink-deep">
            </div>

            <div class="pt-2">
                <label class="flex items-center space-x-2.5 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" {{ old('is_default', $address->is_default) ? 'checked' : '' }}
                        class="w-4 h-4 rounded text-pink-deep focus:ring-pink-deep border-line">
                    <span class="text-xs text-ink">Jadikan sebagai alamat pengiriman utama</span>
                </label>
            </div>

            <div class="pt-4 border-t border-line flex items-center justify-end space-x-3">
                <a href="{{ route('addresses.index') }}"
                    class="px-4 py-2.5 border border-line text-muted hover:text-ink text-sm font-medium rounded-xl transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2.5 bg-pink-deep hover:bg-pink-mauve text-white text-sm font-medium rounded-xl shadow-card transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
</x-layouts.app>