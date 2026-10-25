<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShipmentTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.rajaongkir.key' => 'shipping-cost-test-key',
            'services.rajaongkir.base_url' => 'https://rajaongkir.komerce.id/api/v1',
            'services.rajaongkir.timeout' => 5,
            'services.rajaongkir.connect_timeout' => 2,
            'cache.default' => 'array',
        ]);

        Http::preventStrayRequests();
    }

    public function test_empty_resi_is_rejected_without_calling_provider(): void
    {
        $admin = User::factory()->admin()->create();
        [$order] = $this->makeShipment(trackingNumber: null);

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->post(route('admin.orders.tracking.refresh', $order))
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('error');

        Http::assertNothingSent();
    }

    public function test_non_admin_cannot_refresh_tracking(): void
    {
        $customer = User::factory()->customer()->create();
        [$order] = $this->makeShipment();

        $this->actingAs($customer)->post(route('admin.orders.tracking.refresh', $order))->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_tracking_is_not_requested_before_order_is_shipped(): void
    {
        $admin = User::factory()->admin()->create();
        [$order] = $this->makeShipment(status: 'packed', shipmentStatus: 'pending');

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->post(route('admin.orders.tracking.refresh', $order))
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('error');

        Http::assertNothingSent();
    }

    public function test_admin_refreshes_tracking_and_customer_sees_cached_timeline(): void
    {
        $admin = User::factory()->admin()->create();
        [$order, $owner] = $this->makeShipment();
        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/track/waybill' => Http::response($this->trackingResponse(), 200),
        ]);

        $this->actingAs($admin)->post(route('admin.orders.tracking.refresh', $order))
            ->assertRedirect()
            ->assertSessionHas('success');

        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request->url() === 'https://rajaongkir.komerce.id/api/v1/track/waybill'
            && $request->hasHeader('key', 'shipping-cost-test-key')
            && str_contains((string) $request->body(), 'awb=RESI-TRACK-123')
            && str_contains((string) $request->body(), 'courier=jne')
            && str_contains((string) $request->body(), 'last_phone_number=67890'));

        $this->actingAs($owner)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('ON PROCESS')
            ->assertSee('Paket diterima di hub Bandung')
            ->assertSee('Bandung')
            ->assertDontSee('Alamat penerima rahasia');

        $this->assertSame('shipped', $order->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(15000.0, (float) $order->fresh()->shipping_cost);
        Http::assertSentCount(1);
    }

    public function test_unknown_provider_status_is_shown_verbatim_without_changing_order_state(): void
    {
        $admin = User::factory()->admin()->create();
        [$order, $owner] = $this->makeShipment();
        $response = $this->trackingResponse();
        $response['data']['summary']['status'] = 'COURIER_STATUS_X9';
        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/track/waybill' => Http::response($response, 200),
        ]);

        $this->actingAs($admin)->post(route('admin.orders.tracking.refresh', $order))->assertRedirect();
        $this->actingAs($owner)->get(route('orders.show', $order))->assertSee('COURIER_STATUS_X9');

        $this->assertSame('shipped', $order->fresh()->status);
    }

    public function test_tracking_timeout_fails_safely_and_preserves_order(): void
    {
        $admin = User::factory()->admin()->create();
        [$order] = $this->makeShipment();
        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/track/waybill' => function () {
                throw new ConnectionException('private timeout details');
            },
        ]);

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->post(route('admin.orders.tracking.refresh', $order))
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('error', 'Layanan ongkir sedang tidak tersedia. Silakan coba lagi.');

        $this->assertSame('shipped', $order->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_provider_error_and_rate_limit_do_not_leak_response_or_overwrite_order(): void
    {
        $admin = User::factory()->admin()->create();
        [$order] = $this->makeShipment();
        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/track/waybill' => Http::response([
                'meta' => ['message' => 'sensitive provider diagnostic', 'code' => 429, 'status' => 'error'],
                'data' => null,
            ], 429),
        ]);

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->post(route('admin.orders.tracking.refresh', $order))
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('error', 'Permintaan pelacakan terlalu sering. Silakan coba kembali beberapa saat lagi.')
            ->assertSessionMissing('sensitive provider diagnostic');

        $this->assertSame('shipped', $order->fresh()->status);
        $this->assertSame(15000.0, (float) $order->fresh()->shipping_cost);
    }

    private function makeShipment(
        string $status = 'shipped',
        string $shipmentStatus = 'shipped',
        ?string $trackingNumber = 'RESI-TRACK-123',
    ): array {
        $owner = User::factory()->customer()->create();
        $order = Order::factory()->create([
            'user_id' => $owner->id,
            'status' => $status,
            'payment_status' => 'paid',
            'shipping_phone' => '+62 812-3456-7890',
        ]);
        Shipment::factory()->create([
            'order_id' => $order->id,
            'courier' => 'JNE',
            'courier_code' => 'jne',
            'service' => 'REG',
            'tracking_number' => $trackingNumber,
            'status' => $shipmentStatus,
        ]);

        return [$order->fresh(['shipment']), $owner];
    }

    /** @return array<string, mixed> */
    private function trackingResponse(): array
    {
        return [
            'meta' => ['message' => 'success', 'code' => 200, 'status' => 'success'],
            'data' => [
                'delivered' => false,
                'summary' => [
                    'status' => 'ON PROCESS',
                    'receiver_name' => 'Pelanggan Internal',
                    'destination' => 'Alamat penerima rahasia',
                ],
                'manifest' => [[
                    'manifest_code' => 'HUB',
                    'manifest_description' => 'Paket diterima di hub Bandung',
                    'manifest_date' => '2026-10-25',
                    'manifest_time' => '12:30:00',
                    'city_name' => 'Bandung',
                ]],
                'details' => ['receiver_address1' => 'Alamat penerima rahasia'],
            ],
        ];
    }
}
