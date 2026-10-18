<x-layouts.auth>
    <x-slot:title>Atur Ulang Kata Sandi - MH Scarf Store</x-slot:title>

    @php
        $input = 'h-11 w-full rounded-full border bg-cream/50 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-pink-deep focus-visible:ring-2 focus-visible:ring-pink-deep/40';
        $eyeBtn = 'absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-full text-muted transition hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep/40';
    @endphp

    <main class="flex min-h-[100svh] w-full flex-col items-center justify-center bg-cream px-4 py-10">

        <a href="{{ route('home') }}" class="mb-6 rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep" aria-label="MH Scarf Store beranda">
            <span class="font-display text-lg font-bold uppercase tracking-[0.18em] text-ink">MH Scarf</span>
        </a>

        <div class="w-full max-w-[400px] rounded-3xl border border-line bg-white p-6 shadow-sm sm:p-8">

            <h1 class="font-display text-2xl font-bold text-ink">Kata sandi baru</h1>
            <p class="mt-1 text-sm text-muted">Buat kata sandi baru untuk akun MH Scarf Store Anda.</p>

            <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $email) }}" required autocomplete="email"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        class="{{ $input }} px-4 {{ $errors->has('email') ? 'border-rose-300' : 'border-line' }}"
                        placeholder="nama@email.com">
                    @error('email')<p class="mt-1.5 text-xs text-rose-700" role="alert">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-ink">Kata sandi baru</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required minlength="8" autofocus autocomplete="new-password"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                            class="{{ $input }} pl-4 pr-12 {{ $errors->has('password') ? 'border-rose-300' : 'border-line' }}"
                            placeholder="Minimal 8 karakter">
                        <button type="button" data-toggle-password="password" class="{{ $eyeBtn }}" aria-label="Tampilkan kata sandi">
                            <svg data-eye-open class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            <svg data-eye-closed class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m3 3 18 18M10.6 6.2A10.4 10.4 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-3.1 3.8M6.2 6.7C3.9 8.4 2.5 12 2.5 12s3.5 6 9.5 6c1.5 0 2.8-.4 3.9-.9"/></svg>
                        </button>
                    </div>
                    @error('password')<p class="mt-1.5 text-xs text-rose-700" role="alert">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-ink">Konfirmasi kata sandi</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8" autocomplete="new-password"
                            class="{{ $input }} pl-4 pr-12 border-line"
                            placeholder="Ulangi kata sandi baru">
                        <button type="button" data-toggle-password="password_confirmation" class="{{ $eyeBtn }}" aria-label="Tampilkan konfirmasi kata sandi">
                            <svg data-eye-open class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            <svg data-eye-closed class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m3 3 18 18M10.6 6.2A10.4 10.4 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-3.1 3.8M6.2 6.7C3.9 8.4 2.5 12 2.5 12s3.5 6 9.5 6c1.5 0 2.8-.4 3.9-.9"/></svg>
                        </button>
                    </div>
                </div>

                <x-primary-button type="submit" class="w-full rounded-full">Simpan kata sandi baru</x-primary-button>
            </form>

            <p class="mt-6 text-center text-sm text-muted">
                <a href="{{ route('login') }}" class="font-medium text-pink-deep transition hover:text-pink-mauve">← Kembali ke halaman masuk</a>
            </p>
        </div>
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