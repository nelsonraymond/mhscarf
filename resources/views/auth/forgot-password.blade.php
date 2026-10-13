<x-layouts.auth>
    <x-slot:title>Lupa Kata Sandi - MH Scarf Store</x-slot:title>

    <div class="mb-6 text-center">
        <h1 class="font-serif-display text-2xl font-bold text-ink">Lupa Kata Sandi</h1>
        <p class="text-sm text-muted mt-1.5">Masukkan email Anda untuk menerima tautan pengaturan ulang kata sandi</p>
    </div>

    @if (session('status'))
        <div class="mb-5 p-3.5 rounded-xl bg-pink-soft/40 border border-line text-xs text-ink flex items-start gap-2">
            <span class="text-pink-deep font-semibold" aria-hidden="true">✓</span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4" novalidate>
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-ink mb-1">Email Terdaftar</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                autocomplete="email"
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl border text-sm text-ink bg-cream/60 placeholder:text-muted/60 focus:outline-none focus:ring-2 focus:ring-pink-deep focus:border-transparent transition
                    {{ $errors->has('email') ? 'border-rose-300' : 'border-line' }}"
                placeholder="nama@email.com">
            @error('email')
                <p class="mt-1.5 text-xs text-rose-700" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="w-full py-3 px-4 rounded-xl font-medium text-white bg-pink-deep hover:bg-pink-mauve shadow-sm hover:shadow transition duration-200 cursor-pointer text-sm min-h-[48px]">
            Kirim Tautan Reset
        </button>
    </form>

    <div class="mt-6 text-center text-sm text-muted">
        Ingat kata sandi Anda?
        <a href="{{ route('login') }}" class="text-pink-deep hover:text-pink-mauve font-medium ml-1">
            Masuk Kembali
        </a>
    </div>
</x-layouts.auth>
