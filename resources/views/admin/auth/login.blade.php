<x-layouts.auth>
    <x-slot:title>Admin Login - MH Scarf Store</x-slot:title>

    <div class="mb-6 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-[#FFF9F5] border border-[#EBDDE2] text-[#D98FAF] mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>
        <h1 class="font-serif-display text-2xl font-bold text-[#3A3033]">Portal Administrator</h1>
        <p class="text-sm text-[#75686D] mt-1">Masuk untuk mengelola inventaris dan pesanan</p>
    </div>

    @if ($errors->any())
        <div class="mb-5 p-3.5 rounded-xl bg-pink-50 border border-pink-200 text-sm text-[#75686D]">
            <ul class="list-disc list-inside space-y-1 text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-[#3A3033] mb-1">Email Administrator</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] focus:border-transparent text-sm text-[#3A3033] bg-[#FFF9F5]/40"
                placeholder="admin@MH Scarfstore.com">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-[#3A3033] mb-1">Kata Sandi</label>
            <input type="password" name="password" id="password" required
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#EBDDE2] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] focus:border-transparent text-sm text-[#3A3033] bg-[#FFF9F5]/40"
                placeholder="••••••••">
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center text-[#75686D]">
                <input type="checkbox" name="remember" class="rounded border-[#EBDDE2] text-[#D98FAF] focus:ring-[#D98FAF] mr-2">
                Ingat sesi ini
            </label>
        </div>

        <button type="submit"
            class="w-full py-2.5 px-4 rounded-xl font-medium text-white bg-[#3A3033] hover:bg-[#524449] shadow-sm hover:shadow transition duration-200 cursor-pointer text-sm">
            Masuk sebagai Admin
        </button>
    </form>

    <div class="mt-6 text-center text-xs text-[#75686D]">
        <a href="{{ route('home') }}" class="text-[#75686D] hover:text-[#D98FAF] transition">
            ← Kembali ke Beranda Toko
        </a>
    </div>
</x-layouts.auth>
