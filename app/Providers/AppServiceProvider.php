<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Mail\Transport\TimedResendTransport;
use App\Services\Payments\MidtransPaymentGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PaymentGatewayInterface::class,
            MidtransPaymentGateway::class
        );
    }

    public function boot(): void
    {
        Mail::extend('resend_timed', function (array $config) {
            return new TimedResendTransport(
                apiKey: (string) config('services.resend.key', ''),
                baseUrl: (string) config('services.resend.base_url', 'api.resend.com'),
                timeout: (int) config('services.resend.timeout', 8),
                connectTimeout: (int) config('services.resend.connect_timeout', 3),
            );
        });

        if ($this->app->environment('production')) {
            \URL::forceScheme('https');

            if (config('mail.default') === 'log') {
                Log::warning('Transactional email is configured to use the log mailer in production; emails will not be delivered.');
            }
        }

        RateLimiter::for('admin-login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by($request->ip().'|'.$email),
                Limit::perHour(20)->by($request->ip()),
            ];
        });

        RateLimiter::for('transactional-email-test', function (Request $request) {
            $adminId = $request->user()?->getAuthIdentifier() ?? 'guest';

            return Limit::perMinute(1)
                ->by($adminId . '|' . $request->ip());
        });
    }
}
