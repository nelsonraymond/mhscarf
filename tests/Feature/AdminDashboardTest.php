<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_operational_summary_and_excludes_unpaid_orders_from_revenue(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Owner MH Scarf',
            'email' => 'owner@example.com',
        ]);

        Order::factory()->create([
            'status' => 'pending',
            'payment_status' => 'pending',
            'grand_total' => 200000,
        ]);

        Order::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'grand_total' => 150000,
        ]);

        Order::factory()->create([
            'status' => 'cancelled',
            'payment_status' => 'paid',
            'grand_total' => 99000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Ringkasan operasional MH Scarf');
        $response->assertSeeInOrder(['Pendapatan lunas', 'Rp 150.000']);
$response->assertDontSee('Rp 350.000');
$response->assertDontSee('Rp 249.000');
$response->assertDontSee('Rp 449.000');
        $response->assertSee('Perlu ditindaklanjuti');
    }
}
