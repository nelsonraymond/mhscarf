<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Daftar Alamat Pengiriman - MH Scarf Store</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

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

            <nav class="hidden md:flex items-center space-x-6 text-sm">
                <a href="{{ route('home') }}" class="text-[#75686D] hover:text-[#D98FAF] transition">Beranda</a>
                <a href="{{ route('shop.index') }}" class="text-[#75686D] hover:text-[#D98FAF] transition">Koleksi Produk</a>
                <a href="{{ route('orders.index') }}" class="text-[#75686D] hover:text-[#D98FAF] transition">Pesanan Saya</a>
            </nav>

            <div class="flex items-center space-x-4 text-sm">
                <a href="{{ route('wishlist.index') }}" class="text-[#75686D] hover:text-[#D98FAF] text-xs font-medium flex items-center">
                    <span class="mr-1">♡</span> Wishlist
                </a>
                <a href="{{ route('cart.index') }}" class="text-[#75686D] hover:text-[#D98FAF] text-xs font-medium flex items-center">
                    <span class="mr-1">🛒</span> Keranjang
                </a>
                <a href="{{ route('profile') }}" class="text-[#D98FAF] font-semibold text-xs flex items-center">
                    Akun
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10">
        <!-- Breadcrumb / Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <div class="flex items-center space-x-2 text-xs text-[#75686D] mb-1">
                    <a href="{{ route('profile') }}" class="hover:underline">Akun</a>
                    <span>/</span>
                    <span class="text-[#3A3033] font-medium">Buku Alamat</span>
                </div>
                <h1 class="font-serif-display text-2xl sm:text-3xl font-bold text-[#3A3033]">Alamat Pengiriman</h1>
                <p class="text-xs sm:text-sm text-[#75686D] mt-1">Kelola daftar alamat untuk kemudahan proses checkout pesanan Anda.</p>
            </div>
            <div>
                <a href="{{ route('addresses.create') }}"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-[#D98FAF] hover:bg-[#c97e9e] text-white text-sm font-medium rounded-xl shadow-xs transition">
                    <span class="mr-1.5 text-base">+</span> Tambah Alamat Baru
                </a>
            </div>
        </div>

        <!-- Success Alert -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-emerald-500">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if($addresses->isEmpty())
            <div class="bg-white rounded-2xl border border-[#EBDDE2] p-10 text-center shadow-xs">
                <div class="w-16 h-16 mx-auto rounded-full bg-[#FFF9F5] border border-[#EBDDE2] flex items-center justify-center text-2xl text-[#D98FAF] mb-4">
                    📍
                </div>
                <h2 class="font-serif-display text-xl font-bold text-[#3A3033] mb-2">Belum Ada Alamat Tersimpan</h2>
                <p class="text-sm text-[#75686D] max-w-md mx-auto mb-6">
                    Tambahkan alamat pengiriman utama Anda untuk mempercepat proses pemesanan hijab favorit Anda di MH Scarf Store.
                </p>
                <a href="{{ route('addresses.create') }}"
                    class="inline-flex items-center px-5 py-2.5 bg-[#D98FAF] hover:bg-[#c97e9e] text-white text-sm font-medium rounded-xl transition">
                    + Tambah Alamat Sekarang
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach($addresses as $address)
                    <div class="bg-white rounded-2xl border {{ $address->is_default ? 'border-[#D98FAF] shadow-sm ring-1 ring-[#D98FAF]/30' : 'border-[#EBDDE2]' }} p-6 flex flex-col justify-between transition hover:border-[#D98FAF]/70">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                    <span class="font-semibold text-[#3A3033] text-base">{{ $address->recipient_name }}</span>
                                    @if($address->label)
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#FFF9F5] border border-[#EBDDE2] text-[#75686D]">
                                            {{ $address->label }}
                                        </span>
                                    @endif
                                </div>
                                @if($address->is_default)
                                    <span class="shrink-0 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#D98FAF]/15 text-[#D98FAF] border border-[#D98FAF]/30">
                                        Utama
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-[#75686D] font-mono mb-2">{{ $address->phone }}</p>

                            <p class="text-sm text-[#3A3033] leading-relaxed mb-3">
                                {{ $address->fullAddress() }}
                            </p>

                            @if($address->notes)
                                <div class="p-2.5 rounded-lg bg-[#FFF9F5] border border-[#EBDDE2]/70 text-xs text-[#75686D] italic mb-4">
                                    Catatan: {{ $address->notes }}
                                </div>
                            @endif
                        </div>

                        <div class="pt-4 border-t border-[#EBDDE2] flex items-center justify-between text-xs mt-2">
                            <div>
                                @if(! $address->is_default)
                                    <form method="POST" action="{{ route('addresses.default', $address) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[#D98FAF] hover:text-[#c97e9e] font-medium underline underline-offset-2">
                                            Jadikan Utama
                                        </button>
                                    </form>
                                @else
                                    <span class="text-emerald-600 font-medium flex items-center">
                                        <span class="mr-1">✓</span> Alamat Pengiriman Utama
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center space-x-3">
                                <a href="{{ route('addresses.edit', $address) }}" class="text-[#75686D] hover:text-[#3A3033] font-medium">
                                    Ubah
                                </a>

                                <form method="POST" action="{{ route('addresses.destroy', $address) }}"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus alamat ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 font-medium">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>

    <footer class="mt-auto bg-white border-t border-[#EBDDE2] py-6 text-center text-xs text-[#75686D]">
        <p>&copy; {{ date('Y') }} MH Scarf Store. Soft Luxury Hijab Boutique.</p>
    </footer>
</body>
</html>
