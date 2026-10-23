<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        // User menekan "Batal" di layar Google
        if ($request->has('error')) {
            return $this->fail('Login dengan Google dibatalkan.');
        }

        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::warning('Google login gagal', ['error' => $e->getMessage()]);

            return $this->fail('Login dengan Google gagal. Silakan coba lagi.');
        }

        $email = $google->getEmail();
        $emailVerified = (bool) ($google->user['email_verified'] ?? $google->user['verified_email'] ?? false);

        if (! $email || ! $emailVerified) {
            return $this->fail('Email Google Anda belum terverifikasi.');
        }

        $user = User::where('google_id', $google->getId())->first();

        if (! $user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                // Jangan pernah menautkan Google ke akun admin
                if ($user->role !== 'customer') {
                    return $this->fail('Akun ini tidak dapat masuk dengan Google.');
                }

                $user->forceFill([
                    'google_id' => $google->getId(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            } else {
                $user = new User;
                $user->forceFill([
                    'name' => $google->getName() ?: Str::before($email, '@'),
                    'email' => $email,
                    'google_id' => $google->getId(),
                    'role' => 'customer',
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(40)),
                ])->save();
            }
        } elseif ($user->role !== 'customer') {
            return $this->fail('Akun ini tidak dapat masuk dengan Google.');
        }

        // Ambil identitas keranjang tamu SEBELUM session diregenerasi
        $oldSessionId = $request->session()->getId();
        $guestCartId = $request->session()->get('cart_id');

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        $this->cartService->mergeGuestCart($oldSessionId, $user, $guestCartId);
        $request->session()->forget('cart_id');

        return redirect()->intended(route('home'));
    }

    protected function fail(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
