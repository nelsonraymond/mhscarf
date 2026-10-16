<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRefundTest extends TestCase
{
    use RefreshDatabase;

    private function orderWithRefundPayment(): Order
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'cancelled',
            'payment_status' => 'paid',
            'grand_total' => 115000,
        ]);

        $order->payments()->create([
            'provider' => 'midtrans',
            'payment_method' => 'bank_transfer',
            'transaction_reference' => 'TRX-REFUND-1',
            'amount' => 115000,
            'status' => 'paid',
            'paid_at' => now(),
            'metadata' => [
                'needs_refund' => true,
                'needs_refund_reason' => 'Pesanan dibatalkan setelah pembayaran diterima.',
            ],
        ]);

        return $order;
    }

    public function test_dashboard_shows_refund_alert_only_when_needed(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()->assertDontSee('perlu direfund');

        $order = $this->orderWithRefundPayment();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('1 pesanan perlu direfund')
            ->assertSee($order->order_number);
    }

    public function test_order_page_shows_refund_panel(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->orderWithRefundPayment();

        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Pesanan ini perlu direfund')
            ->assertSee('Tandai sudah direfund');
    }

    public function test_admin_can_mark_order_as_refunded(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->orderWithRefundPayment();

        $this->actingAs($admin)->post(route('admin.orders.refunded', $order))
            ->assertRedirect()->assertSessionHas('success');

        $payment = $order->payments()->first();
        $this->assertSame('refunded', $payment->status);
        $this->assertFalse($payment->metadata['needs_refund']);
        $this->assertSame($admin->id, $payment->metadata['refunded_marked_by']);
        $this->assertSame('refunded', $order->fresh()->payment_status);

        // Setelah ditandai, banner hilang
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee('perlu direfund');
    }

    public function test_marking_refund_without_flag_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create(['payment_status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.orders.refunded', $order))
            ->assertRedirect()->assertSessionHas('error');
    }

    public function test_customer_cannot_mark_refund(): void
    {
        $customer = User::factory()->customer()->create();
        $order = $this->orderWithRefundPayment();

        $status = $this->actingAs($customer)->post(route('admin.orders.refunded', $order))->status();

        $this->assertContains($status, [302, 403]);
        $this->assertTrue($order->payments()->first()->metadata['needs_refund']);
    }

    public function test_cancelling_a_paid_order_flags_refund(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
        $order->payments()->create([
            'provider' => 'midtrans', 'payment_method' => 'qris', 'transaction_reference' => 'TRX-PAID-1',
            'amount' => $order->grand_total, 'status' => 'paid', 'paid_at' => now(), 'metadata' => [],
        ]);

        $this->actingAs($admin)->post(route('admin.orders.cancel', $order));

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertTrue($order->payments()->first()->metadata['needs_refund']);
    }
}