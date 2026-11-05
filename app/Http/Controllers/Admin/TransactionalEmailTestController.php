<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TransactionalEmailTestMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TransactionalEmailTestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        abort_unless(config('mail.transactional_test.enabled'), 404);

        $recipient = config('mail.transactional_test.recipient');

        if (! is_string($recipient) || filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            return response()->json(['message' => 'Email test is not configured.'], 503);
        }

        try {
            Mail::mailer('resend_timed')
                ->to($recipient)
                ->send(new TransactionalEmailTestMail());
        } catch (Throwable) {
            Log::warning('Transactional email test delivery failed.');

            return response()->json(['message' => 'Test email could not be sent.'], 503);
        }

        return response()->json(['message' => 'Test email sent.']);
    }
}
