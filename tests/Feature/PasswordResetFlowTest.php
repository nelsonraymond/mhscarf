<?php

namespace Tests\Feature;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_email_receives_a_resend_password_reset_mailable(): void
    {
        Mail::fake();
        $user = User::factory()->customer()->create(['email' => 'reset@example.com']);

        $response = $this->post(route('password.email'), ['email' => $user->email]);

        $response->assertRedirect()->assertSessionHas('status');
        Mail::assertSent(PasswordResetMail::class, fn (PasswordResetMail $mail): bool =>
            $mail->mailer === 'resend_timed'
            && $mail->hasTo($user->email)
            && str_contains($mail->resetUrl, '/reset-password/')
            && str_contains($mail->render(), $mail->resetUrl)
        );

        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotNull($record);
        $resetUrl = Mail::sent(PasswordResetMail::class)->first()->resetUrl;
        $rawToken = basename((string) parse_url($resetUrl, PHP_URL_PATH));
        $this->assertTrue(Hash::check($rawToken, $record->token));
    }

    public function test_unknown_email_gets_the_same_positive_response_without_sending_mail(): void
    {
        Mail::fake();

        $response = $this->post(route('password.email'), ['email' => 'unknown@example.com']);

        $response->assertRedirect()->assertSessionHas('status');
        Mail::assertNothingSent();
    }

    public function test_reset_token_is_valid_once_password_is_changed_and_remember_token_rotates(): void
    {
        Mail::fake();
        $user = User::factory()->customer()->create(['password' => 'old-password']);
        $oldRememberToken = $user->remember_token;
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('login'))->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertNotSame($oldRememberToken, $user->fresh()->remember_token);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'another-password-123',
            'password_confirmation' => 'another-password-123',
        ])->assertSessionHasErrors('email');
    }

    public function test_expired_reset_token_is_rejected(): void
    {
        $user = User::factory()->customer()->create();
        $token = Password::broker()->createToken($user);
        $this->travel(61)->minutes();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasErrors('email');

        $this->assertFalse(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_password_validation_rejects_short_and_unconfirmed_values(): void
    {
        $user = User::factory()->customer()->create();
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['password']);
    }

    public function test_reset_request_rate_limit_is_enforced(): void
    {
        Mail::fake();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('password.email'), ['email' => 'unknown@example.com'])->assertRedirect();
        }

        $this->post(route('password.email'), ['email' => 'unknown@example.com'])->assertTooManyRequests();
    }

    public function test_reset_mail_failure_does_not_reveal_whether_email_is_registered(): void
    {
        $user = User::factory()->customer()->create();
        Mail::shouldReceive('mailer')->once()->with('resend_timed')->andThrow(new \RuntimeException('provider failure'));

        $response = $this->post(route('password.email'), ['email' => $user->email]);

        $response->assertRedirect()->assertSessionHas('status');
    }

    public function test_old_authenticated_session_is_rejected_after_password_changes(): void
    {
        $user = User::factory()->customer()->create(['password' => 'old-password']);
        $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'old-password',
        ])->assertRedirect();
        $this->get(route('profile'))->assertOk();
        $this->assertAuthenticatedAs($user);

        $token = Password::broker()->createToken($user->fresh());
        Password::broker()->reset([
            'email' => $user->email,
            'token' => $token,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ], function (User $resetUser, string $password): void {
            $resetUser->forceFill([
                'password' => $password,
                'remember_token' => str_repeat('r', 60),
            ])->save();
        });

        Auth::forgetGuards();
        $this->get(route('profile'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
