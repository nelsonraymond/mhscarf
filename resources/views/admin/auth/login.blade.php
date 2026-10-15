<x-layouts.auth>
    <x-slot:title>Admin Login - MH Scarf Store</x-slot:title>

    <div class="flex min-h-screen items-center justify-center bg-admin-bg px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-lg bg-admin-sidebar text-sm font-bold text-white">
                    MH
                </div>
                <h1 class="text-2xl font-semibold tracking-tight text-admin-text">Portal Administrator</h1>
                <p class="mt-1 text-sm text-admin-muted">Masuk untuk mengelola katalog, pesanan, dan ulasan MH Scarf Store.</p>
            </div>

            <div class="admin-card p-6 sm:p-8">
                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-3.5 py-3 text-sm text-red-800" role="alert">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="admin-label">Email administrator</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            class="admin-input @error('email') admin-input-error @enderror"
                            placeholder="admin@example.com">
                        @error('email')
                            <p class="admin-field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="admin-label">Kata sandi</label>
                        <input type="password" name="password" id="password" required autocomplete="current-password"
                            class="admin-input @error('password') admin-input-error @enderror"
                            placeholder="••••••••">
                        @error('password')
                            <p class="admin-field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center text-sm text-admin-muted">
                        <input type="checkbox" name="remember" class="mr-2 rounded border-admin-border text-admin-accent focus:ring-admin-accent">
                        Ingat sesi ini
                    </label>

                    <button type="submit" class="admin-btn admin-btn-primary w-full">
                        Masuk sebagai Admin
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-admin-muted">
                <a href="{{ route('home') }}" class="hover:text-admin-accent hover:underline">← Kembali ke beranda toko</a>
            </p>
        </div>
    </div>
</x-layouts.auth>
