<?php

namespace App\Http\Controllers\Cron;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ReviewReminderCronController
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = config('services.cron.secret');
        $authorization = (string) $request->header('Authorization', '');
        $provided = str_starts_with($authorization, 'Bearer ')
            ? substr($authorization, 7)
            : '';

        abort_unless(is_string($secret) && $secret !== '' && hash_equals($secret, $provided), 404);

        $exitCode = Artisan::call('reviews:send-reminders');

        return response()->json(
            ['status' => $exitCode === 0 ? 'ok' : 'failed'],
            $exitCode === 0 ? 200 : 500,
        );
    }
}
