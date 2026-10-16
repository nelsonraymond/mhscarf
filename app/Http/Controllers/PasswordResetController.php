<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Rules\Recaptcha;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a reset link to the given user.
     * Rate-limited to prevent brute-force abuse.
     * Does NOT leak whether an email exists (always returns the same friendly status message).
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
    'email' => ['required', 'email'],
    'g-recaptcha-response' => (app()->environment('testing') || ! config('services.recaptcha.enabled'))
        ? ['nullable']
        : ['required', new Recaptcha],
], [
    'email.required' => 'Email wajib diisi.',
    'email.email' => 'Format email tidak valid.',
    'g-recaptcha-response.required' => 'Silakan centang "Saya bukan robot" terlebih dahulu.',
]);

        // Broker generates token and triggers CanResetPassword::sendPasswordResetNotification()
        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        // Always show the same positive message regardless of whether the email exists in DB
        // to prevent user enumeration attacks.
        return back()->with('status', 'Jika alamat email terdaftar, tautan pengaturan ulang kata sandi telah dikirimkan.');
    }

    /**
     * Display the password reset form with the given token.
     */
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Reset the given user's password.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Kata sandi berhasil diperbarui. Silakan masuk.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
