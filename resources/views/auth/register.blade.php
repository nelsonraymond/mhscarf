<x-layouts.auth>
    <x-slot:title>Daftar Akun - MH Scarf</x-slot:title>

    @php
        $input = 'h-12 w-full rounded-xl border bg-cream/40 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-pink-deep focus-visible:ring-2 focus-visible:ring-pink-deep';
        $eyeBtn = 'absolute inset-y-0 right-0 flex w-12 items-center justify-center text-muted hover:text-pink-deep transition rounded-r-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep';
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
                <p class="text-[11px] uppercase tracking-[0.2em] text-pink-mauve font-semibold mb-4">Bergabung Bersama Kami</p>
                <h2 class="font-display text-4xl xl:text-5xl font-bold leading-[1.15] text-ink">
                    Mulai perjalanan gaya Anda bersama MH Scarf.
                </h2>
                <p class="mt-5 max-w-md text-sm leading-7 text-muted">
                    Buat akun untuk menyimpan wishlist, mengikuti status pesanan, dan menemukan koleksi hijab terbaru.
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
                    <p class="text-[11px] uppercase tracking-[0.2em] text-pink-mauve font-semibold mb-1">Akun Baru</p>
                    <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink">Buat akun Anda</h1>
                    <p class="text-sm text-muted mt-1.5">Bergabunglah dan nikmati koleksi hijab terbaik.</p>
                </div>

                <x-google-button label="Daftar dengan Google" />

                <div class="my-6 flex items-center gap-3 text-[11px] font-medium uppercase tracking-[0.14em] text-muted">
                    <span class="h-px flex-1 bg-line"></span>
                    <span class="whitespace-nowrap">atau dengan email</span>
                    <span class="h-px flex-1 bg-line"></span>
                </div>

                <form method="POST" action="{{ route('register.submit') }}" class="space-y-4" novalidate>
                    @csrf

                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium text-ink">Nama lengkap</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus autocomplete="name" maxlength="100"
                            aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                            class="{{ $input }} px-4 {{ $errors->has('name') ? 'border-rose-300' : 'border-line' }}"
                            placeholder="Alya Nurhaliza">
                        @error('name')<p class="mt-1.5 text-xs text-rose-700" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email" maxlength="150"
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                            class="{{ $input }} px-4 {{ $errors->has('email') ? 'border-rose-300' : 'border-line' }}"
                            placeholder="alya@email.com">
                        @error('email')<p class="mt-1.5 text-xs text-rose-700" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="phone" class="mb-1.5 block text-sm font-medium text-ink">Nomor WhatsApp / HP <span class="font-normal text-muted">(opsional)</span></label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" maxlength="30"
                            aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                            class="{{ $input }} px-4 {{ $errors->has('phone') ? 'border-rose-300' : 'border-line' }}"
                            placeholder="08123456789">
                        @error('phone')<p class="mt-1.5 text-xs text-rose-700" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-ink">Kata sandi</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required autocomplete="new-password" minlength="8"
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
                            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                                class="{{ $input }} pl-4 pr-12 border-line"
                                placeholder="Ulangi kata sandi">
                            <button type="button" data-toggle-password="password_confirmation" class="{{ $eyeBtn }}" aria-label="Tampilkan konfirmasi kata sandi">
                                <svg data-eye-open class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                <svg data-eye-closed class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m3 3 18 18M10.6 6.2A10.4 10.4 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-3.1 3.8M6.2 6.7C3.9 8.4 2.5 12 2.5 12s3.5 6 9.5 6c1.5 0 2.8-.4 3.9-.9"/></svg>
                            </button>
                        </div>
                    </div>
                    <x-recaptcha />
                    <x-primary-button type="submit" class="w-full !mt-6">Buat Akun</x-primary-button>
                </form>

                <p class="mt-7 text-center text-sm text-muted">
                    Sudah memiliki akun?
                    <a href="{{ route('login') }}" class="ml-1 font-medium text-pink-deep hover:text-pink-mauve transition">Masuk di sini</a>
                </p>

                <p class="mt-8 text-center text-[11px] leading-5 text-muted/80">
                    Dengan mendaftar, Anda menyetujui Syarat &amp; Ketentuan<br>dan Kebijakan Privasi MH Scarf Store.
                </p>
            </div>
        </main>
    </div>

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