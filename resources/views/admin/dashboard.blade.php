<x-layouts.admin>
    <x-slot:title>Dashboard - MH Scarf Admin</x-slot:title>
    <x-slot:header>Ringkasan Dashboard</x-slot:header>

    <div class="mb-8">
        <h1 class="font-serif-display text-2xl font-bold text-[#3A3033]">Admin Dashboard</h1>
        <p class="text-sm text-[#75686D] mt-1">Selamat Datang, {{ auth()->user()->name }} &bull; Panel administrasi e-commerce MH Scarf Hijab</p>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl border border-[#EBDDE2] shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs uppercase font-medium tracking-wider text-[#75686D]">Total Kategori</span>
                    <div class="text-2xl font-bold text-[#3A3033] mt-1">{{ \App\Models\Category::count() }}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-pink-50 text-[#D98FAF] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="mt-4 inline-block text-xs font-medium text-[#D98FAF] hover:text-[#B97897]">
                Kelola Kategori →
            </a>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-[#EBDDE2] shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs uppercase font-medium tracking-wider text-[#75686D]">Total Produk</span>
                    <div class="text-2xl font-bold text-[#3A3033] mt-1">{{ \App\Models\Product::count() }}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-pink-50 text-[#D98FAF] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <a href="{{ route('admin.products.index') }}" class="mt-4 inline-block text-xs font-medium text-[#D98FAF] hover:text-[#B97897]">
                Kelola Produk →
            </a>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-[#EBDDE2] shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs uppercase font-medium tracking-wider text-[#75686D]">Total Varian & Stok</span>
                    <div class="text-2xl font-bold text-[#3A3033] mt-1">{{ \App\Models\ProductVariant::sum('stock_qty') }} pcs</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-pink-50 text-[#D98FAF] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            </div>
            <span class="mt-4 inline-block text-xs text-[#75686D]">
                {{ \App\Models\ProductVariant::count() }} varian terdaftar
            </span>
        </div>
    </div>

    <!-- Admin Profile Info Card (preserves prompt requirements) -->
    <div class="bg-white rounded-2xl border border-[#EBDDE2] p-6 shadow-xs">
        <h3 class="font-serif-display text-lg font-semibold text-[#3A3033] mb-4">Informasi Akun Administrator</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div class="p-3.5 rounded-xl bg-[#FFF9F5] border border-[#EBDDE2]">
                <span class="text-xs text-[#75686D] block">Nama Admin</span>
                <span class="font-medium text-[#3A3033] mt-0.5 block">{{ auth()->user()->name }}</span>
            </div>
            <div class="p-3.5 rounded-xl bg-[#FFF9F5] border border-[#EBDDE2]">
                <span class="text-xs text-[#75686D] block">Email</span>
                <span class="font-medium text-[#3A3033] mt-0.5 block">{{ auth()->user()->email }}</span>
            </div>
            <div class="p-3.5 rounded-xl bg-[#FFF9F5] border border-[#EBDDE2]">
                <span class="text-xs text-[#75686D] block">Role</span>
                <span class="inline-flex items-center text-xs font-semibold text-[#D98FAF] bg-pink-100 px-2 py-0.5 rounded-md mt-0.5">
                    {{ ucfirst(auth()->user()->role) }}
                </span>
            </div>
        </div>
    </div>
</x-layouts.admin>
