<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Ubah Alamat Pengiriman - MH Scarf Store</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FFF9F5;
            color: #3A3033;
        }
        .font-serif-display {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-[#FFF9F5]">
    <!-- Navbar -->
    <header class="bg-white border-b border-[#EBDDE2] sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="inline-flex items-center space-x-2">
                <span class="text-[#D98FAF] text-lg">❀</span>
                <span class="font-serif-display text-xl font-bold tracking-wider text-[#3A3033] uppercase">MH Scarf</span>
            </a>

            <div class="flex items-center space-x-4 text-sm">
                <a href="{{ route('addresses.index') }}" class="text-[#75686D] hover:text-[#3A3033] text-xs font-medium">
                    ← Kembali ke Buku Alamat
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-2xl w-full mx-auto px-4 sm:px-6 py-8 md:py-10">
        <div class="mb-6">
            <h1 class="font-serif-display text-2xl font-bold text-[#3A3033]">Ubah Alamat Pengiriman</h1>
            <p class="text-xs text-[#75686D] mt-1">Perbarui informasi penerima dan tujuan pengiriman.</p>
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

        <div class="bg-white rounded-2xl border border-[#EBDDE2] p-6 sm:p-8 shadow-xs">
            <form method="POST" action="{{ route('addresses.update', $address) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="label" class="block text-xs font-medium text-[#75686D] mb-1">Label Alamat (Opsional)</label>
                        <input type="text" id="label" name="label" value="{{ old('label', $address->label) }}" placeholder="Contoh: Rumah, Kantor"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] bg-[#FFF9F5]/40 text-sm focus:outline-none focus:border-[#D98FAF] focus:ring-1 focus:ring-[#D98FAF]">
                    </div>

                    <div>
                        <label for="recipient_name" class="block text-xs font-medium text-[#75686D] mb-1">Nama Penerima <span class="text-rose-500">*</span></label>
                        <input type="text" id="recipient_name" name="recipient_name" value="{{ old('recipient_name', $address->recipient_name) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] bg-[#FFF9F5]/40 text-sm focus:outline-none focus:border-[#D98FAF] focus:ring-1 focus:ring-[#D98FAF]">
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-xs font-medium text-[#75686D] mb-1">Nomor Telepon / WhatsApp <span class="text-rose-500">*</span></label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $address->phone) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] bg-[#FFF9F5]/40 text-sm focus:outline-none focus:border-[#D98FAF] focus:ring-1 focus:ring-[#D98FAF]">
                </div>

                <div>
                    <label for="address_line" class="block text-xs font-medium text-[#75686D] mb-1">Alamat Lengkap <span class="text-rose-500">*</span></label>
                    <textarea id="address_line" name="address_line" rows="3" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] bg-[#FFF9F5]/40 text-sm focus:outline-none focus:border-[#D98FAF] focus:ring-1 focus:ring-[#D98FAF]">{{ old('address_line', $address->address_line) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="district" class="block text-xs font-medium text-[#75686D] mb-1">Kecamatan</label>
                        <input type="text" id="district" name="district" value="{{ old('district', $address->district) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] bg-[#FFF9F5]/40 text-sm focus:outline-none focus:border-[#D98FAF] focus:ring-1 focus:ring-[#D98FAF]">
                    </div>

                    <div>
                        <label for="village" class="block text-xs font-medium text-[#75686D] mb-1">Kelurahan / Desa</label>
                        <input type="text" id="village" name="village" value="{{ old('village', $address->village) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] bg-[#FFF9F5]/40 text-sm focus:outline-none focus:border-[#D98FAF] focus:ring-1 focus:ring-[#D98FAF]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="city" class="block text-xs font-medium text-[#75686D] mb-1">Kota / Kabupaten <span class="text-rose-500">*</span></label>
                        <input type="text" id="city" name="city" value="{{ old('city', $address->city) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] bg-[#FFF9F5]/40 text-sm focus:outline-none focus:border-[#D98FAF] focus:ring-1 focus:ring-[#D98FAF]">
                    </div>

                    <div>
                        <label for="province" class="block text-xs font-medium text-[#75686D] mb-1">Provinsi <span class="text-rose-500">*</span></label>
                        <input type="text" id="province" name="province" value="{{ old('province', $address->province) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] bg-[#FFF9F5]/40 text-sm focus:outline-none focus:border-[#D98FAF] focus:ring-1 focus:ring-[#D98FAF]">
                    </div>

                    <div>
                        <label for="postal_code" class="block text-xs font-medium text-[#75686D] mb-1">Kode Pos <span class="text-rose-500">*</span></label>
                        <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $address->postal_code) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] bg-[#FFF9F5]/40 text-sm focus:outline-none focus:border-[#D98FAF] focus:ring-1 focus:ring-[#D98FAF]">
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-medium text-[#75686D] mb-1">Catatan Tambahan / Patokan (Opsional)</label>
                    <input type="text" id="notes" name="notes" value="{{ old('notes', $address->notes) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] bg-[#FFF9F5]/40 text-sm focus:outline-none focus:border-[#D98FAF] focus:ring-1 focus:ring-[#D98FAF]">
                </div>

                <div class="pt-2">
                    <label class="flex items-center space-x-2.5 cursor-pointer">
                        <input type="checkbox" name="is_default" value="1" {{ old('is_default', $address->is_default) ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-[#D98FAF] focus:ring-[#D98FAF] border-[#EBDDE2]">
                        <span class="text-xs text-[#3A3033]">Jadikan sebagai alamat pengiriman utama</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-[#EBDDE2] flex items-center justify-end space-x-3">
                    <a href="{{ route('addresses.index') }}"
                        class="px-4 py-2.5 border border-[#EBDDE2] text-[#75686D] hover:text-[#3A3033] text-sm font-medium rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-[#D98FAF] hover:bg-[#c97e9e] text-white text-sm font-medium rounded-xl shadow-xs transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
