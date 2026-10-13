<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin Panel' }} - MH Scarf Store</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FBF8F9;
            color: #3A3033;
        }
        .font-serif-display {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col md:flex-row antialiased">
    <!-- Admin Sidebar -->
    <aside class="w-full md:w-64 bg-white border-r border-[#EBDDE2] flex flex-col shrink-0">
        <div class="p-5 border-b border-[#EBDDE2] flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center space-x-2">
                <span class="text-[#D98FAF] text-lg">❀</span>
                <span class="font-serif-display text-lg font-bold tracking-wider text-[#3A3033] uppercase">MH Scarf ADMIN</span>
            </a>
        </div>

        <nav class="flex-1 p-4 space-y-1">
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-pink-50 text-[#D98FAF] font-semibold' : 'text-[#75686D] hover:bg-[#FFF9F5] hover:text-[#3A3033]' }}">
                <svg class="w-5 h-5 mr-3 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <a href="{{ route('admin.categories.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.categories.*') ? 'bg-pink-50 text-[#D98FAF] font-semibold' : 'text-[#75686D] hover:bg-[#FFF9F5] hover:text-[#3A3033]' }}">
                <svg class="w-5 h-5 mr-3 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Kategori
            </a>

            <a href="{{ route('admin.products.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.products.*') ? 'bg-pink-50 text-[#D98FAF] font-semibold' : 'text-[#75686D] hover:bg-[#FFF9F5] hover:text-[#3A3033]' }}">
                <svg class="w-5 h-5 mr-3 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Produk
            </a>

            <a href="{{ route('admin.orders.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.orders.*') ? 'bg-pink-50 text-[#D98FAF] font-semibold' : 'text-[#75686D] hover:bg-[#FFF9F5] hover:text-[#3A3033]' }}">
                <svg class="w-5 h-5 mr-3 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Pesanan
            </a>

            <div class="pt-4 mt-4 border-t border-[#EBDDE2]">
                <a href="{{ route('shop.index') }}" target="_blank"
                    class="flex items-center px-3.5 py-2.5 text-xs text-[#75686D] hover:text-[#3A3033] hover:bg-[#FFF9F5] rounded-xl transition">
                    <svg class="w-4 h-4 mr-2.5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Lihat Toko Publik ↗
                </a>
            </div>
        </nav>

        <div class="p-4 border-t border-[#EBDDE2]">
            <div class="flex items-center justify-between">
                <div class="truncate mr-2">
                    <div class="text-xs font-semibold text-[#3A3033] truncate">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] text-[#75686D] uppercase tracking-wider">Admin</div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" title="Keluar" class="p-1.5 rounded-lg text-[#75686D] hover:text-red-600 hover:bg-red-50 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Nav -->
        <header class="bg-white border-b border-[#EBDDE2] px-6 py-3.5 flex items-center justify-between">
            <h2 class="text-sm font-medium text-[#75686D]">{{ $header ?? 'Manajemen Toko' }}</h2>
            <div class="text-xs text-[#75686D]">
                MH Scarf Store &copy; {{ date('Y') }}
            </div>
        </header>

        <!-- Page Body -->
        <main class="flex-1 p-6 md:p-8 max-w-7xl w-full mx-auto">
            @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-800 flex items-center">
                    <svg class="w-5 h-5 mr-2.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-800">
                    <div class="font-medium mb-1">Terjadi kesalahan pada input:</div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</body>
</html>
