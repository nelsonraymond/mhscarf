<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class Recaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Lewati saat testing agar test auth yang sudah ada tidak pecah
        if (app()->environment('testing') || ! config('services.recaptcha.enabled')) {
            return;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);

            if ($response->json('success') === true) {
                return;
            }

            Log::info('reCAPTCHA ditolak', ['errors' => $response->json('error-codes')]);
        } catch (Throwable $e) {
            Log::warning('reCAPTCHA tidak dapat diverifikasi', ['error' => $e->getMessage()]);
        }

        // Gagal verifikasi atau Google tidak terjangkau: tolak
        $fail('Verifikasi reCAPTCHA gagal. Silakan coba lagi.');
    }
}