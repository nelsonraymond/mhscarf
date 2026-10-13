<x-layouts.auth>
    <x-slot:title>Atur Ulang Kata Sandi - MH Scarf Store</x-slot:title>

    <div class="mb-6 text-center">
        <h1 class="font-serif-display text-2xl font-bold text-ink">Kata Sandi Baru</h1>
        <p class="text-sm text-muted mt-1.5">Buat kata sandi baru untuk akun MH Scarf Store Anda</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="block text-sm font-medium text-ink mb-1">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $email) }}" required autofocus
                autocomplete="email"
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl border text-sm text-ink bg-cream/60 placeholder:text-muted/60 focus:outline-none focus:ring-2 focus:ring-pink-deep focus:border-transparent transition
                    {{ $errors->has('email') ? 'border-rose-300' : 'border-line' }}"
                placeholder="nama@email.com">
            @error('email')
                <p class="mt-1.5 text-xs text-rose-700" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-ink mb-1">Kata Sandi Baru</label>
            <input type="password" name="password" id="password" required minlength="8"
                autocomplete="new-password"
                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl border text-sm text-ink bg-cream/60 placeholder:text-muted/60 focus:outline-none focus:ring-2 focus:ring-pink-deep focus:border-transparent transition
                    {{ $errors->has('password') ? 'border-rose-300' : 'border-line' }}"
                placeholder="Minimal 8 karakter">
            @error('password')
                <p class="mt-1.5 text-xs text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-ink mb-1">Konfirmasi Kata Sandi</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                autocomplete="new-password"
                class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl border border-line text-sm text-ink bg-cream/60 placeholder:text-muted/60 focus:outline-none focus:ring-2 focus:ring-pink-deep focus:border-transparent transition"
                placeholder="Ulangi kata sandi baru">
        </div>

        <button type="submit"
            class="w-full py-3 px-4 rounded-xl font-medium text-white bg-pink-deep hover:bg-pink-mauve shadow-sm hover:shadow transition duration-200 cursor-pointer text-sm min-h-[48px]">
            Simpan Kata Sandi Baru
        </button>
    </form>
</x-layouts.auth>
