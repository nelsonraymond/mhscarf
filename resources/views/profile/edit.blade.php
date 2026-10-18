<x-layouts.app>
    @section('title', 'Edit Profil — MH Scarf')

    @php
        $inputClass = 'mt-1.5 block w-full h-11 px-3.5 rounded-xl border border-line bg-cream/40 text-sm text-ink placeholder:text-muted/70 focus:outline-none focus:border-pink-deep focus-visible:ring-2 focus-visible:ring-pink-deep transition';
    @endphp

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="text-xs text-muted mb-6">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded transition">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('profile') }}" class="hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded transition">Profil Saya</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-ink font-medium">Edit Profil</li>
            </ol>
        </nav>

        <div class="mb-8">
            <p class="text-[11px] uppercase tracking-[0.2em] text-pink-mauve font-semibold mb-1">Pengaturan Akun</p>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink">Edit Profil</h1>
            <p class="text-sm text-muted mt-1.5">Perbarui data pribadi dan kata sandi akun Anda.</p>
        </div>

        {{-- Ringkasan error --}}
        @if ($errors->any())
            <div role="alert" aria-live="assertive" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm text-rose-800">
                <p class="font-semibold mb-1">Periksa kembali:</p>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('PATCH')

            {{-- Data pribadi --}}
            <section class="bg-white rounded-2xl border border-line p-5 sm:p-7 shadow-card" aria-labelledby="pf-personal-h">
                <div class="flex items-center gap-2.5 pb-4 border-b border-line mb-5">
                    <span class="w-8 h-8 rounded-lg bg-pink-soft/40 text-pink-deep flex items-center justify-center" aria-hidden="true">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </span>
                    <h2 id="pf-personal-h" class="font-display text-base font-bold text-ink">Data Pribadi</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-muted">Nama Lengkap</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                            autocomplete="name"
                            class="{{ $inputClass }} {{ $errors->has('name') ? 'border-rose-300' : '' }}">
                        @error('name')<p class="text-xs text-rose-600 mt-1.5" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-muted">Alamat Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                            autocomplete="email"
                            class="{{ $inputClass }} {{ $errors->has('email') ? 'border-rose-300' : '' }}">
                        @error('email')<p class="text-xs text-rose-600 mt-1.5" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-muted">
                            Telepon / WhatsApp <span class="normal-case font-normal tracking-normal">(opsional)</span>
                        </label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" maxlength="20"
                            autocomplete="tel" placeholder="08xxxxxxxxxx"
                            class="{{ $inputClass }} {{ $errors->has('phone') ? 'border-rose-300' : '' }}">
                        @error('phone')<p class="text-xs text-rose-600 mt-1.5" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            {{-- Keamanan --}}
            <section class="bg-white rounded-2xl border border-line p-5 sm:p-7 shadow-card" aria-labelledby="pf-security-h">
                <div class="flex items-center gap-2.5 pb-4 border-b border-line mb-5">
                    <span class="w-8 h-8 rounded-lg bg-pink-soft/40 text-pink-deep flex items-center justify-center" aria-hidden="true">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    </span>
                    <div>
                        <h2 id="pf-security-h" class="font-display text-base font-bold text-ink">Keamanan</h2>
                        <p class="text-xs text-muted">Kosongkan bila tidak ingin mengganti kata sandi.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-muted">Password Baru</label>
                        <input type="password" name="password" id="password" minlength="8"
                            autocomplete="new-password" placeholder="Minimal 8 karakter"
                            class="{{ $inputClass }} {{ $errors->has('password') ? 'border-rose-300' : '' }}">
                        @error('password')<p class="text-xs text-rose-600 mt-1.5" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-muted">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            autocomplete="new-password" placeholder="Ulangi password baru"
                            class="{{ $inputClass }}">
                    </div>
                </div>

                <label class="inline-flex items-center gap-2 mt-4 text-xs text-muted cursor-pointer select-none">
                    <input type="checkbox" id="toggle-password" class="w-4 h-4 accent-pink-deep rounded">
                    Tampilkan password
                </label>
            </section>

            {{-- Aksi --}}
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                <a href="{{ route('profile') }}"
                    class="inline-flex items-center justify-center px-5 h-12 rounded-xl border border-line bg-white hover:bg-cream text-sm font-medium text-ink transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
                    Batal
                </a>
                <x-primary-button type="submit" class="w-full sm:w-auto sm:min-w-[200px]">
                    Simpan Perubahan
                </x-primary-button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('toggle-password')?.addEventListener('change', function () {
            var type = this.checked ? 'text' : 'password';
            document.getElementById('password').type = type;
            document.getElementById('password_confirmation').type = type;
        });
    </script>
</x-layouts.app>