<x-layouts.auth>
    <x-slot:title>Lupa Kata Sandi - MH Scarf</x-slot:title>

    @php
        $input = 'h-11 w-full rounded-full border bg-cream/50 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-pink-deep focus-visible:ring-2 focus-visible:ring-pink-deep/40';
    @endphp

    <main class="flex min-h-[100svh] w-full flex-col items-center justify-center bg-cream px-4 py-10">

        <a href="{{ route('home') }}" class="mb-6 rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep" aria-label="MH Scarf Store beranda">
            <span class="font-display text-lg font-bold uppercase tracking-[0.18em] text-ink">MH Scarf</span>
        </a>

        <div class="w-full max-w-[400px] rounded-3xl border border-line bg-white p-6 shadow-sm sm:p-8">

            <h1 class="font-display text-2xl font-bold text-ink">Lupa kata sandi</h1>
            <p class="mt-1 text-sm text-muted">Masukkan email Anda untuk menerima tautan pengaturan ulang kata sandi.</p>

            @if (session('status'))
                <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 text-xs text-emerald-800" role="status">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4" novalidate>
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email terdaftar</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        class="{{ $input }} px-4 {{ $errors->has('email') ? 'border-rose-300' : 'border-line' }}"
                        placeholder="nama@email.com">
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <x-recaptcha />
                <x-primary-button type="submit" class="w-full rounded-full">Kirim tautan reset</x-primary-button>
            </form>

            <p class="mt-6 text-center text-sm text-muted">
                Ingat kata sandi Anda?
                <a href="{{ route('login') }}" class="ml-1 font-medium text-pink-deep transition hover:text-pink-mauve">Masuk kembali</a>
            </p>
        </div>
    </main>
</x-layouts.auth>