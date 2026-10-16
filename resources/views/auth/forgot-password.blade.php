<x-layouts.auth>
    <x-slot:title>Lupa Kata Sandi - MH Scarf Store</x-slot:title>

    @php
        $input = 'h-12 w-full rounded-xl border bg-cream/40 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-pink-deep focus-visible:ring-2 focus-visible:ring-pink-deep';
    @endphp

    <div class="min-h-[100svh] w-full bg-cream lg:grid lg:grid-cols-[1.05fr_0.95fr]">

        {{-- Panel brand (desktop) --}}
        <aside class="relative hidden min-h-[100svh] overflow-hidden bg-pink-soft/40 lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16">
            <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-pink-soft/60" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-40 -left-32 h-[28rem] w-[28rem] rounded-full border-[42px] border-pink-soft/50" aria-hidden="true"></div>
            <svg class="pointer-events-none absolute -bottom-6 -right-6 h-72 w-72 text-pink-deep opacity-20" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="0.8" aria-hidden="true">
                <path d="M50 90 C50 60 50 40 50 20"/>
                <path d="M50 55 C35 50 28 38 30 25 C45 30 52 42 50 55Z"/>
                <path d="M50 45 C65 40 72 28 70 15 C55 20 48 32 50 45Z"/>
                <circle cx="50" cy="16" r="5"/><circle cx="42" cy="12" r="4"/><circle cx="58" cy="12" r="4"/>
            </svg>

            <a href="{{ route('home') }}" class="relative z-10 w-fit rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep" aria-label="MH Scarf Store beranda">
                <span class="font-display text-xl font-bold tracking-[0.18em] uppercase text-ink">MH Scarf</span>
            </a>

            <div class="relative z-10 max-w-lg">
                <p class="text-[11px] uppercase tracking-[0.2em] text-pink-mauve font-semibold mb-4">Elegance in Every Wrap</p>
                <h2 class="font-display text-4xl xl:text-5xl font-bold leading-[1.15] text-ink">
                    Tenang, kami bantu Anda kembali masuk.
                </h2>
                <p class="mt-5 max-w-md text-sm leading-7 text-muted">
                    Masukkan email akun Anda, lalu ikuti tautan yang kami kirim untuk membuat kata sandi baru.
                </p>
            </div>

            <p class="relative z-10 text-xs text-muted">© {{ date('Y') }} MH Scarf Store</p>
        </aside>

        {{-- Panel form --}}
        <main class="flex min-h-[100svh] w-full items-center justify-center px-4 py-8 sm:px-8 sm:py-10 lg:px-12">
            <div class="w-full max-w-md">

                <a href="{{ route('home') }}" class="inline-block mb-8 lg:hidden rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep" aria-label="MH Scarf Store beranda">
                    <span class="font-display text-lg font-bold tracking-[0.18em] uppercase text-ink">MH Scarf</span>
                </a>

                <div class="mb-7">
                    <p class="text-[11px] uppercase tracking-[0.2em] text-pink-mauve font-semibold mb-1">Pemulihan Akun</p>
                    <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink">Lupa Kata Sandi</h1>
                    <p class="text-sm text-muted mt-1.5">Masukkan email Anda untuk menerima tautan pengaturan ulang kata sandi.</p>
                </div>

                @if (session('status'))
                    <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 text-xs text-emerald-800" role="status">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-5" novalidate>
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

                    <x-primary-button type="submit" class="w-full">Kirim Tautan Reset</x-primary-button>
                </form>

                <p class="mt-7 text-center text-sm text-muted">
                    Ingat kata sandi Anda?
                    <a href="{{ route('login') }}" class="ml-1 font-medium text-pink-deep hover:text-pink-mauve transition">Masuk kembali</a>
                </p>
            </div>
        </main>
    </div>
</x-layouts.auth>