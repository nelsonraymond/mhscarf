<x-layouts.auth>
    <x-slot:title>Masuk - MH Scarf Store</x-slot:title>

    <div class="mb-6 text-center">
        <h1 class="font-serif-display text-2xl font-bold text-ink">Selamat Datang</h1>
        <p class="text-sm text-muted mt-1.5">Masuk ke akun Anda untuk melanjutkan belanja</p>
    </div>

    <form method="POST" action="{{ route('login.submit') }}" class="space-y-4" novalidate>
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-ink mb-1">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                autocomplete="username"
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl border text-sm text-ink bg-cream/60 placeholder:text-muted/60 focus:outline-none focus:ring-2 focus:ring-pink-deep focus:border-transparent transition
                    {{ $errors->has('email') ? 'border-rose-300' : 'border-line' }}"
                placeholder="nama@email.com">
            @error('email')
                {{-- Authentication failures are surfaced here too: CustomerAuthController maps
                     failed Auth::attempt() onto the 'email' key via ValidationException. --}}
                <p class="mt-1.5 text-xs text-rose-700" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-ink mb-1">Kata Sandi</label>
            {{-- Intentionally no old() value: password is never re-displayed after a failed validation --}}
            <input type="password" name="password" id="password" required
                autocomplete="current-password"
                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl border text-sm text-ink bg-cream/60 placeholder:text-muted/60 focus:outline-none focus:ring-2 focus:ring-pink-deep focus:border-transparent transition
                    {{ $errors->has('password') ? 'border-rose-300' : 'border-line' }}"
                placeholder="••••••••">
            @error('password')
                <p class="mt-1.5 text-xs text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center text-muted cursor-pointer">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                    autocomplete="off"
                    class="rounded border-line text-pink-deep focus:ring-pink-deep mr-2">
                Ingat saya
            </label>
            <a href="{{ route('password.request') }}" class="text-xs text-pink-deep hover:text-pink-mauve font-medium">
                Lupa kata sandi?
            </a>
        </div>

        <button type="submit"
            class="w-full py-3 px-4 rounded-xl font-medium text-white bg-pink-deep hover:bg-pink-mauve shadow-sm hover:shadow transition duration-200 cursor-pointer text-sm min-h-[48px]">
            Masuk
        </button>
    </form>

    <div class="mt-6 text-center text-sm text-muted">
        Belum punya akun?
        <a href="{{ route('register') }}" class="text-pink-deep hover:text-pink-mauve font-medium ml-1">
            Daftar Sekarang
        </a>
    </div>
</x-layouts.auth>
