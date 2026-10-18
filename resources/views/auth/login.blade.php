<x-layouts.auth>
    <x-slot:title>Masuk - MH Scarf Store</x-slot:title>

    @php
        $input = 'h-11 w-full rounded-full border bg-cream/50 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-pink-deep focus-visible:ring-2 focus-visible:ring-pink-deep/40';
    @endphp

    <main class="flex min-h-[100svh] w-full flex-col items-center justify-center bg-cream px-4 py-10">

        <a href="{{ route('home') }}" class="mb-6 rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep" aria-label="MH Scarf Store beranda">
            <span class="font-display text-lg font-bold uppercase tracking-[0.18em] text-ink">MH Scarf</span>
        </a>

        <div class="w-full max-w-[400px] rounded-3xl border border-line bg-white p-6 shadow-sm sm:p-8">

            <h1 class="font-display text-2xl font-bold text-ink">Masuk</h1>

            @if (session('success'))
                <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 text-xs text-emerald-800" role="status">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="mt-6 space-y-4" novalidate>
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        class="{{ $input }} px-4 {{ $errors->has('email') ? 'border-rose-300' : 'border-line' }}"
                        placeholder="nama@email.com">
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-ink">Kata sandi</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required autocomplete="current-password"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                            class="{{ $input }} pl-4 pr-12 {{ $errors->has('password') ? 'border-rose-300' : 'border-line' }}"
                            placeholder="Masukkan kata sandi">
                        <button type="button" data-toggle-password="password"
                            class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-full text-muted transition hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep/40"
                            aria-label="Tampilkan kata sandi">
                            <svg data-eye-open class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            <svg data-eye-closed class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m3 3 18 18M10.6 6.2A10.4 10.4 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-3.1 3.8M6.2 6.7C3.9 8.4 2.5 12 2.5 12s3.5 6 9.5 6c1.5 0 2.8-.4 3.9-.9"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between gap-3">
                    <label class="flex cursor-pointer items-center gap-2 text-xs text-muted">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} autocomplete="off"
                            class="h-4 w-4 rounded border-line accent-pink-deep">
                        <span>Ingat saya</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="whitespace-nowrap text-xs font-medium text-pink-deep transition hover:text-pink-mauve">Lupa kata sandi?</a>
                </div>

                <x-recaptcha />
                <x-primary-button type="submit" class="w-full rounded-full">Masuk</x-primary-button>
            </form>

            <div class="my-5 flex items-center gap-3 text-[11px] font-medium uppercase tracking-[0.14em] text-muted">
                <span class="h-px flex-1 bg-line"></span>
                <span>atau</span>
                <span class="h-px flex-1 bg-line"></span>
            </div>

            <x-google-button />

            <p class="mt-6 text-center text-sm text-muted">
                Belum punya akun?
                <a href="{{ route('register') }}" class="ml-1 font-medium text-pink-deep transition hover:text-pink-mauve">Daftar</a>
            </p>
        </div>

        <p class="mt-6 max-w-[400px] text-center text-[11px] leading-5 text-muted/80">
            Dengan masuk, Anda menyetujui Syarat &amp; Ketentuan dan Kebijakan Privasi MH Scarf Store.
        </p>
    </main>

    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById(button.dataset.togglePassword);
                var showing = input.type === 'password';
                input.type = showing ? 'text' : 'password';
                button.querySelector('[data-eye-open]').classList.toggle('hidden', showing);
                button.querySelector('[data-eye-closed]').classList.toggle('hidden', !showing);
                button.setAttribute('aria-label', showing ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            });
        });
    </script>
</x-layouts.auth>