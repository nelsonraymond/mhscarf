<?php

namespace Tests\Feature;

use App\Mail\TransactionalEmailTestMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TransactionalEmailTestEndpointTest extends TestCase
{
    use RefreshDatabase;

    private const ROUTE = 'admin.transactional-email-test.store';

    private const RECIPIENT = 'nelsonraymondwu@gmail.com';

    protected function setUp(): void
    {
        parent::setUp();

        config(['mail.transactional_test.recipient' => self::RECIPIENT]);
    }

    public function test_guest_cannot_access_the_test_endpoint(): void
    {
        Mail::fake();

        $response = $this->postJson(route(self::ROUTE));

        $response->assertUnauthorized();
        Mail::assertNothingOutgoing();
    }

    public function test_customer_cannot_access_the_test_endpoint(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->customer()->create());

        $this->postJson(route(self::ROUTE))->assertForbidden();

        Mail::assertNothingOutgoing();
    }

    public function test_admin_cannot_send_when_the_feature_flag_is_disabled(): void
    {
        Mail::fake();
        config(['mail.transactional_test.enabled' => false]);
        $this->actingAs(User::factory()->admin()->create());

        $this->postJson(route(self::ROUTE))->assertNotFound();

        Mail::assertNothingOutgoing();
    }

    public function test_admin_can_send_test_mail_using_configured_recipient_and_timed_mailer_without_business_changes(): void
    {
        Mail::fake();
        config(['mail.transactional_test.enabled' => true]);
        $this->actingAs(User::factory()->admin()->create());
        $before = $this->businessRecordCounts();

        $this->postJson(route(self::ROUTE), ['recipient' => 'attacker@example.com'])
            ->assertOk()
            ->assertExactJson(['message' => 'Test email sent.']);

        Mail::assertSent(TransactionalEmailTestMail::class, function (TransactionalEmailTestMail $mail): bool {
            return $mail->hasTo(self::RECIPIENT)
                && $mail->mailer === 'resend_timed'
                && $mail->envelope()->subject === 'MH SCARF — Transactional Email Test'
                && str_contains($mail->render(), 'does not indicate that a customer transaction has taken place');
        });
        Mail::assertSentTimes(TransactionalEmailTestMail::class, 1);
        $this->assertSame($before, $this->businessRecordCounts());
    }

    public function test_rate_limit_allows_only_one_test_send_per_minute_for_an_admin_and_ip(): void
    {
        Mail::fake();
        config(['mail.transactional_test.enabled' => true]);
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->postJson(route(self::ROUTE))->assertOk();
        $this->postJson(route(self::ROUTE))->assertTooManyRequests();

        Mail::assertSentTimes(TransactionalEmailTestMail::class, 1);
    }

    public function test_transport_failure_returns_generic_response_and_logs_no_exception_details(): void
    {
        config(['mail.transactional_test.enabled' => true]);
        $this->actingAs(User::factory()->admin()->create());
        Mail::shouldReceive('mailer')->once()->with('resend_timed')->andThrow(new \RuntimeException('sensitive provider detail'));
        Log::shouldReceive('warning')->once()->with('Transactional email test delivery failed.');

        $this->postJson(route(self::ROUTE))
            ->assertStatus(503)
            ->assertExactJson(['message' => 'Test email could not be sent.'])
            ->assertDontSee('sensitive provider detail');
    }

    public function test_endpoint_is_post_only_and_remains_in_web_csrf_middleware_group(): void
    {
        $route = app('router')->getRoutes()->getByName(self::ROUTE);

        $this->assertSame(['POST'], $route->methods());
        $this->assertContains('web', $route->gatherMiddleware());
    }

    /** @return array<string, int> */
    private function businessRecordCounts(): array
    {
        return collect(['orders', 'payments', 'shipments', 'carts', 'product_variants', 'inventory_movements'])
            ->mapWithKeys(fn (string $table): array => [$table => DB::table($table)->count()])
            ->all();
    }
}
