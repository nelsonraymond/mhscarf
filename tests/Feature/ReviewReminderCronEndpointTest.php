<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ReviewReminderCronEndpointTest extends TestCase
{
    public function test_cron_endpoint_rejects_missing_or_invalid_authorization(): void
    {
        config(['services.cron.secret' => 'test-cron-secret']);

        $this->getJson(route('cron.review-reminders'))->assertNotFound();
        $this->withHeader('Authorization', 'Bearer wrong-secret')
            ->getJson(route('cron.review-reminders'))
            ->assertNotFound();

    }

    public function test_cron_endpoint_runs_the_reminder_command_with_the_configured_secret(): void
    {
        config(['services.cron.secret' => 'test-cron-secret']);
        Artisan::shouldReceive('call')->once()->with('reviews:send-reminders')->andReturn(0);

        $this->withHeader('Authorization', 'Bearer test-cron-secret')
            ->getJson(route('cron.review-reminders'))
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }

    public function test_cron_endpoint_is_disabled_when_secret_is_missing(): void
    {
        config(['services.cron.secret' => null]);

        $this->withHeader('Authorization', 'Bearer anything')
            ->getJson(route('cron.review-reminders'))
            ->assertNotFound();
    }
}
