<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Masuk');
    }

    public function test_customer_can_view_register_page(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Buat akun Anda');
    }

    public function test_customer_registration_succeeds(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Alya Nurhaliza',
            'email' => 'alya@example.com',
            'phone' => '08123456789',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'alya@example.com',
            'name' => 'Alya Nurhaliza',
            'phone' => '08123456789',
            'role' => 'customer',
        ]);

        $user = User::where('email', 'alya@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isCustomer());
        $this->assertFalse($user->isAdmin());
        $this->assertAuthenticatedAs($user);
    }

    public function test_duplicate_email_registration_is_rejected(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->post(route('register.submit'), [
            'name' => 'Duplicate User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_registration_validation_requires_password_confirmation(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Test User',
            'email' => 'test_unconfirmed@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_customer_login_succeeds(): void
    {
        $user = User::factory()->customer()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('secret1234'),
        ]);

        $response = $this->post(route('login.submit'), [
            'email' => 'customer@example.com',
            'password' => 'secret1234',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_customer_login_is_rejected(): void
    {
        User::factory()->customer()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('correct_password'),
        ]);

        $response = $this->post(route('login.submit'), [
            'email' => 'customer@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_customer_logout_succeeds(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_protected_profile_requires_authentication(): void
    {
        $response = $this->get(route('profile'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_customer_can_access_profile(): void
    {
        $user = User::factory()->customer()->create([
            'name' => 'Siti Aminah',
            'email' => 'siti@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('profile'));

        $response->assertStatus(200);
        $response->assertSee('Siti Aminah');
        $response->assertSee('siti@example.com');
        $response->assertSee('Edit Profil');
        $response->assertSee(route('orders.index'));
        $response->assertSee(route('addresses.index'));
        $response->assertSee(route('wishlist.index'));
    }

    public function test_guest_cannot_logout(): void
    {
        $response = $this->post(route('logout'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_view_forgot_password_page(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Lupa Kata Sandi');
    }

    public function test_customer_can_request_password_reset_link(): void
    {
        $user = User::factory()->customer()->create([
            'email' => 'customer_reset@example.com',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'customer_reset@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
    }

    public function test_requesting_reset_for_nonexistent_email_does_not_leak_enumeration(): void
    {
        $response = $this->post(route('password.email'), [
            'email' => 'doesnotexist@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
    }

    public function test_customer_can_view_reset_password_page(): void
    {
        $response = $this->get(route('password.reset', ['token' => 'sample-token', 'email' => 'user@example.com']));

        $response->assertStatus(200);
        $response->assertSee('Kata Sandi Baru');
    }

    public function test_google_callback_creates_and_logs_in_new_customer(): void
    {
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-id-12345');
        $abstractUser->shouldReceive('getName')->andReturn('Google User');
        $abstractUser->shouldReceive('getEmail')->andReturn('googleuser@example.com');
        $abstractUser->user = ['email_verified' => true];

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'googleuser@example.com',
            'google_id' => 'google-id-12345',
            'role' => 'customer',
        ]);

        $user = User::where('email', 'googleuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->password);
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_callback_rejects_admin_user(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-id-admin');
        $abstractUser->shouldReceive('getName')->andReturn('Admin User');
        $abstractUser->shouldReceive('getEmail')->andReturn('admin@example.com');
        $abstractUser->user = ['email_verified' => true];

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }
}
