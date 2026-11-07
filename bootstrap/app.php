<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Console\Commands\ReleaseExpiredOrders;
use App\Console\Commands\SendReviewReminders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
    ReleaseExpiredOrders::class,
    SendReviewReminders::class,
])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->authenticateSessions();
        $middleware->trustProxies(at: '*');

        $middleware->trustHosts(at: fn () => [
            '^mhscarf\.shop$',
            '^www\.mhscarf\.shop$',
            '^mhscarf\.vercel\.app$',
            '^localhost$',
            '^127\.0\.0\.1$',
        ]);

        $middleware->redirectGuestsTo(function (Request $request): string {
            return $request->is('admin*') ? route('admin.login') : route('login');
        });

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/payment',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
