<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVoucherTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'code' => 'HEMAT10',
            'type' => 'percentage',
            'value' => 10,
            'max_discount_amount' => 20000,
            'min_order_amount' => 75000,
            'is_active' => 1,
        ], $override);
    }

    public function test_admin_can_create_voucher_and_code_is_uppercased(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.vouchers.store'), $this->payload(['code' => ' hemat10 ']))
            ->assertRedirect(route('admin.vouchers.index'));

        $this->assertDatabaseHas('vouchers', ['code' => 'HEMAT10', 'type' => 'percentage', 'is_active' => 1]);
    }

    public function test_admin_can_list_vouchers(): void
    {
        Voucher::create(['code' => 'ADA', 'type' => 'fixed', 'value' => 15000, 'is_active' => true]);

        $this->actingAs($this->admin())
            ->get(route('admin.vouchers.index'))
            ->assertOk()
            ->assertSee('ADA');
    }

    public function test_percentage_above_100_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.vouchers.store'), $this->payload(['value' => 150]))
            ->assertSessionHasErrors('value');

        $this->assertSame(0, Voucher::count());
    }

    public function test_duplicate_code_is_rejected(): void
    {
        Voucher::create(['code' => 'HEMAT10', 'type' => 'fixed', 'value' => 1000, 'is_active' => true]);

        $this->actingAs($this->admin())
            ->post(route('admin.vouchers.store'), $this->payload())
            ->assertSessionHasErrors('code');
    }

    public function test_expiry_must_be_after_start(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.vouchers.store'), $this->payload([
                'starts_at' => '2026-12-31 10:00',
                'expires_at' => '2026-12-01 10:00',
            ]))
            ->assertSessionHasErrors('expires_at');
    }

    public function test_quota_cannot_be_lowered_below_used_count(): void
    {
        $voucher = Voucher::create([
            'code' => 'PAKAI', 'type' => 'fixed', 'value' => 1000,
            'usage_limit' => 10, 'used_count' => 5, 'is_active' => true,
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.vouchers.update', $voucher), [
                'code' => 'PAKAI', 'type' => 'fixed', 'value' => 1000, 'usage_limit' => 3, 'is_active' => 1,
            ])
            ->assertSessionHasErrors('usage_limit');
    }

    public function test_admin_can_toggle_voucher(): void
    {
        $voucher = Voucher::create(['code' => 'TOGGLE', 'type' => 'fixed', 'value' => 1000, 'is_active' => true]);

        $this->actingAs($this->admin())->patch(route('admin.vouchers.toggle', $voucher))->assertRedirect();

        $this->assertFalse($voucher->fresh()->is_active);
    }

    public function test_used_voucher_cannot_be_deleted(): void
    {
        $voucher = Voucher::create(['code' => 'DIPAKAI', 'type' => 'fixed', 'value' => 1000, 'is_active' => true]);
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $customer->id]);
        VoucherUsage::create([
            'voucher_id' => $voucher->id, 'user_id' => $customer->id,
            'order_id' => $order->id, 'discount_amount' => 1000,
        ]);

        $this->actingAs($this->admin())->delete(route('admin.vouchers.destroy', $voucher))->assertRedirect();

        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id]);
    }

    public function test_unused_voucher_can_be_deleted(): void
    {
        $voucher = Voucher::create(['code' => 'HAPUS', 'type' => 'fixed', 'value' => 1000, 'is_active' => true]);

        $this->actingAs($this->admin())->delete(route('admin.vouchers.destroy', $voucher))
            ->assertRedirect(route('admin.vouchers.index'));

        $this->assertDatabaseMissing('vouchers', ['id' => $voucher->id]);
    }

    public function test_customer_cannot_access_voucher_admin(): void
    {
        $customer = User::factory()->customer()->create();

        $status = $this->actingAs($customer)->get(route('admin.vouchers.index'))->status();

        $this->assertContains($status, [302, 403]);
        $this->assertSame(0, Voucher::count());
    }
}