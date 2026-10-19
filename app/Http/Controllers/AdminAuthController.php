<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    /** Percobaan gagal per kombinasi email + IP (per menit) dan per IP (per jam). */
    private const MAX_ATTEMPTS_PER_ACCOUNT = 5;

    private const MAX_ATTEMPTS_PER_IP = 20;

    /**
     * Show the admin login form.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle an admin login attempt.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $accountKey = 'admin-login:'.Str::transliterate(Str::lower($credentials['email']).'|'.$request->ip());
        $ipKey = 'admin-login-ip:'.$request->ip();

        foreach ([[$accountKey, self::MAX_ATTEMPTS_PER_ACCOUNT], [$ipKey, self::MAX_ATTEMPTS_PER_IP]] as [$key, $max]) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                $seconds = RateLimiter::availableIn($key);

                throw ValidationException::withMessages([
                    'email' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$seconds} detik.",
                ]);
            }
        }

        // Hanya akun admin yang bisa diautentikasi di sini. Kredensial pelanggan diperlakukan
        // sama seperti kredensial salah: pesan identik dan tidak ada sesi login yang terbentuk.
        // Sengaja tanpa "ingat saya": sesi admin mengikuti SESSION_LIFETIME.
        if (! Auth::attempt([...$credentials, 'role' => 'admin'])) {
            RateLimiter::hit($accountKey, 60);
            RateLimiter::hit($ipKey, 3600);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($accountKey);
        $request->session()->regenerate();

        // Kembali ke halaman admin yang tadi dituju, hanya bila masih di dalam /admin.
        $intended = $request->session()->pull('url.intended');
        $adminRoot = url('admin');

        if ($intended && ($intended === $adminRoot || str_starts_with($intended, $adminRoot.'/'))) {
            return redirect($intended);
        }

        return redirect()->route('admin.dashboard');
    }

    /**
     * Log the admin out.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}