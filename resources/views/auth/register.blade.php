<x-layouts.auth>
    <x-slot:title>Daftar Akun - MH Scarf Store</x-slot:title>

    <div class="mb-6 text-center">
        <h1 class="font-serif-display text-2xl font-bold text-ink">Daftar Akun Baru</h1>
        <p class="text-sm text-muted mt-1.5">Bergabunglah dan nikmati koleksi hijab terbaik</p>
    </div>

    <form method="POST" action="{{ route('register.submit') }}" class="space-y-4" novalidate>
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-ink mb-1">Nama Lengkap</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                autocomplete="name" maxlength="100"
                aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl border text-sm text-ink bg-cream/60 placeholder:text-muted/60 focus:outline-none focus:ring-2 focus:ring-pink-deep focus:border-transparent transition
                    {{ $errors->has('name') ? 'border-rose-300' : 'border-line' }}"
                placeholder="Alya Nurhaliza">
            @error('name')
                <p class="mt-1.5 text-xs text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-ink mb-1">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                autocomplete="email" maxlength="150"
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl border text-sm text-ink bg-cream/60 placeholder:text-muted/60 focus:outline-none focus:ring-2 focus:ring-pink-deep focus:border-transparent transition
                    {{ $errors->has('email') ? 'border-rose-300' : 'border-line' }}"
                placeholder="alya@email.com">
            @error('email')
                <p class="mt-1.5 text-xs text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-ink mb-1">Nomor WhatsApp / HP (Opsional)</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                autocomplete="tel" inputmode="tel" maxlength="30"
                aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl border text-sm text-ink bg-cream/60 placeholder:text-muted/60 focus:outline-none focus:ring-2 focus:ring-pink-deep focus:border-transparent transition
                    {{ $errors->has('phone') ? 'border-rose-300' : 'border-line' }}"
                placeholder="08123456789">
            @error('phone')
                <p class="mt-1.5 text-xs text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-ink mb-1">Kata Sandi</label>
            {{-- Intentionally no old() value: password is never re-displayed after a failed validation --}}
            <input type="password" name="password" id="password" required
                autocomplete="new-password" minlength="8"
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
            <input type="password" name="password_confirmation" id="password_confirmation" required
                autocomplete="new-password"
                class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl border border-line text-sm text-ink bg-cream/60 placeholder:text-muted/60 focus:outline-none focus:ring-2 focus:ring-pink-deep focus:border-transparent transition"
                placeholder="Ulangi kata sandi">
        </div>

        <button type="submit"
            class="w-full py-3 px-4 rounded-xl font-medium text-white bg-pink-deep hover:bg-pink-mauve shadow-sm hover:shadow transition duration-200 cursor-pointer text-sm min-h-[48px]">
            Buat Akun
        </button>
    </form>

    <div class="mt-6 text-center text-sm text-muted">
        Sudah memiliki akun?
        <a href="{{ route('login') }}" class="text-pink-deep hover:text-pink-mauve font-medium ml-1">
            Masuk di sini
        </a>
    </div>
</x-layouts.auth>
