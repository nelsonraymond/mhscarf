<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_admin_login_page(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('Portal Administrator');
    }

    public function test_admin_login_succeeds_and_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@mhscarfstore.com',
            'password' => Hash::make('admin1234'),
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@mhscarfstore.com',
            'password' => 'admin1234',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_customer_credentials_cannot_login_as_admin(): void
    {
        $customer = User::factory()->customer()->create([
            'email' => 'customer@MH Scarfstore.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => 'customer@MH Scarfstore.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_invalid_admin_login_is_rejected(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@MH Scarfstore.com',
            'password' => Hash::make('admin1234'),
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@MH Scarfstore.com',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Owner MH Scarf',
            'email' => 'owner@MH Scarfstore.com',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Owner MH Scarf');
        $response->assertSee('owner@MH Scarfstore.com');
        $response->assertSee('admin');
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_admin_dashboard_and_is_redirected_to_admin_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_logout_succeeds(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_admin_login_is_rate_limited(): void
{
    $route = app('router')->getRoutes()->getByName('admin.login.submit');
    $this->assertNotNull($route);
    $this->assertContains('throttle:admin-login', $route->gatherMiddleware());

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('admin.login.submit'), ['email' => 'owner@example.com', 'password' => 'salah']);
    }

    $this->post(route('admin.login.submit'), ['email' => 'owner@example.com', 'password' => 'salah'])
        ->assertStatus(429);
}

public function test_admin_login_is_locked_after_repeated_failures(): void
{
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('admin.login.submit'), [
            'email' => 'owner@example.com',
            'password' => 'salah',
        ]);
    }

    $response = $this->post(route('admin.login.submit'), [
        'email' => 'owner@example.com',
        'password' => 'salah',
    ]);

    $response->assertStatus(429);
    $this->assertGuest();
}

public function test_customer_credentials_get_the_same_error_and_no_session(): void
{
    $customer = User::factory()->customer()->create(['password' => 'password']);

    $this->post(route('admin.login.submit'), ['email' => $customer->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    $this->assertSame(__('auth.failed'), session('errors')->first('email'));
}
}
