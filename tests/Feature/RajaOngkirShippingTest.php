<?php

namespace Tests\Feature;

use App\Exceptions\ShippingProviderException;
use App\Contracts\PaymentGatewayInterface;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Voucher;
use App\Services\CheckoutService;
use App\Services\RajaOngkirClient;
use App\Services\ShippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class RajaOngkirShippingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.rajaongkir.key' => 'raja-test-secret',
            'services.rajaongkir.base_url' => 'https://rajaongkir.komerce.id/api/v1',
            'services.rajaongkir.origin_id' => 31592,
            'services.rajaongkir.timeout' => 20,
            'services.rajaongkir.connect_timeout' => 10,
            'midtrans.server_key' => 'midtrans-test-key',
            'midtrans.client_key' => 'midtrans-test-client',
        ]);
        Http::preventStrayRequests();
    }

    public function test_verifies_configured_origin_by_all_five_location_fields(): void
    {
        Http::fake([
            '*/destination/domestic-destination*' => Http::response($this->destinationResponse([$this->origin()]), 200),
        ]);

        $this->assertSame(31592, app(ShippingService::class)->verifiedOriginId());
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/destination/domestic-destination?search=Maguwoharjo&limit=100&offset=0')
            && $request->hasHeader('key', 'raja-test-secret'));
    }

    public function test_rejects_origin_candidate_when_any_location_field_does_not_match(): void
    {
        $wrong = $this->origin();
        $wrong['zip_code'] = '55281';
        Http::fake([
            '*/destination/domestic-destination*' => Http::response($this->destinationResponse([$wrong]), 200),
        ]);

        try {
            app(ShippingService::class)->verifiedOriginId();
            $this->fail('A mismatched origin candidate must not be accepted.');
        } catch (ShippingProviderException) {
            $this->assertTrue(true);
        }
        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/calculate/domestic-cost'));
    }

    public function test_destination_matching_requires_subdistrict_district_city_province_and_postal_code(): void
    {
        $valid = $this->destination(17620, 'MENTENG', 'MENTENG', 'JAKARTA PUSAT', 'DKI JAKARTA', '10310');
        $sameCityWrongSubdistrict = $this->destination(17621, 'PEGANGSAAN', 'MENTENG', 'JAKARTA PUSAT', 'DKI JAKARTA', '10320');
        Http::fake([
            '*/destination/domestic-destination*' => Http::response($this->destinationResponse([$valid, $sameCityWrongSubdistrict]), 200),
        ]);

        $matches = app(ShippingService::class)->destinationsForAddress($this->customerAddress());

        $this->assertCount(1, $matches);
        $this->assertSame(17620, $matches[0]['id']);
    }

    public function test_calculation_parses_actual_service_fields_and_posts_form_parameters(): void
    {
        Http::fake([
            '*/calculate/domestic-cost' => Http::response([
                'meta' => ['code' => 200, 'status' => 'success', 'message' => 'Success Calculate Domestic Shipping cost'],
                'data' => [
                    ['name' => 'JNE', 'code' => 'jne', 'service' => 'REG', 'description' => 'Layanan Reguler', 'cost' => 16000, 'etd' => '2 day'],
                    ['name' => 'JNE', 'code' => 'jne', 'service' => 'YES', 'description' => 'Yakin Esok Sampai', 'cost' => 39000, 'etd' => '1 day'],
                ],
            ], 200),
        ]);

        $rates = app(RajaOngkirClient::class)->calculateDomesticCost(31592, 17620, 700, 'jne');

        $this->assertCount(2, $rates);
        $this->assertSame(['name' => 'JNE', 'code' => 'jne', 'service' => 'REG', 'description' => 'Layanan Reguler', 'cost' => 16000, 'etd' => '2 day'], $rates[0]);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/calculate/domestic-cost')
            && $request->hasHeader('key', 'raja-test-secret')
            && str_contains((string) $request->body(), 'origin=31592')
            && str_contains((string) $request->body(), 'destination=17620')
            && str_contains((string) $request->body(), 'weight=700')
            && str_contains((string) $request->body(), 'courier=jne'));
    }

    public function test_http_error_timeout_and_malformed_response_fail_safely(): void
    {
        Http::fake(['*/calculate/domestic-cost' => Http::response(['private' => 'raja-test-secret'], 503)]);
        try {
            app(RajaOngkirClient::class)->calculateDomesticCost(31592, 17620, 700, 'jne');
            $this->fail('HTTP errors must fail closed.');
        } catch (ShippingProviderException $exception) {
            $this->assertStringNotContainsString('raja-test-secret', $exception->getMessage());
        }

        Http::fake(['*/calculate/domestic-cost' => function () {
            throw new ConnectionException('private request details');
        }]);
        try {
            app(RajaOngkirClient::class)->calculateDomesticCost(31592, 17620, 700, 'jne');
            $this->fail('Connection timeouts must fail closed.');
        } catch (ShippingProviderException $exception) {
            $this->assertStringNotContainsString('private request details', $exception->getMessage());
        }

        Http::fake(['*/calculate/domestic-cost' => Http::response(['meta' => ['status' => 'success', 'code' => 200], 'data' => [['service' => 'REG']]], 200)]);
        try {
            app(RajaOngkirClient::class)->calculateDomesticCost(31592, 17620, 700, 'jne');
            $this->fail('Malformed provider data must fail closed.');
        } catch (ShippingProviderException) {
            $this->assertTrue(true);
        }
    }

    public function test_total_weight_uses_every_variant_weight_times_quantity_and_rejects_missing_weight(): void
    {
        $user = User::factory()->customer()->create();
        $first = $this->variant(180);
        $second = $this->variant(275);
        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create(['product_variant_id' => $first->id, 'quantity' => 2, 'unit_price' => $first->finalPrice()]);
        $cart->items()->create(['product_variant_id' => $second->id, 'quantity' => 1, 'unit_price' => $second->finalPrice()]);

        $this->assertSame(635, app(CheckoutService::class)->currentShippingWeight($user));

        $second->update(['weight_gram' => null]);
        try {
            app(CheckoutService::class)->currentShippingWeight($user);
            $this->fail('Missing weight must block checkout.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('shipping_quote_token', $exception->errors());
        }
    }

    public function test_checkout_recalculates_server_tariff_and_ignores_browser_shipping_cost(): void
    {
        Mail::fake();
        $user = User::factory()->customer()->create();
        $address = $this->customerAddress($user);
        $variant = $this->variant(350);
        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 2, 'unit_price' => $variant->finalPrice()]);

        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/destination/domestic-destination')) {
                $search = (string) $request->data()['search'];
                return Http::response($this->destinationResponse([$search === 'Maguwoharjo' ? $this->origin() : $this->destination(17620, 'MENTENG', 'MENTENG', 'JAKARTA PUSAT', 'DKI JAKARTA', '10310')]), 200);
            }
            if (str_ends_with($request->url(), '/calculate/domestic-cost')) {
                return Http::response(['meta' => ['code' => 200, 'status' => 'success'], 'data' => [
                    ['name' => 'JNE', 'code' => 'jne', 'service' => 'REG', 'description' => 'Layanan Reguler', 'cost' => 16000, 'etd' => '2 day'],
                ]], 200);
            }
            return Http::response([], 404);
        });

        $quoteResponse = $this->actingAs($user)->postJson(route('checkout.shipping.quotes'), [
            'address_id' => $address->id,
            'destination_id' => 17620,
            'courier' => 'jne',
        ])->assertOk();
        $quote = $quoteResponse->json('quotes.0');
        $this->assertIsInt($quote['expires_at']);

        $gateway = Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldReceive('getName')->andReturn('test-gateway');
        $gateway->shouldReceive('createPayment')->once()->withArgs(function (Order $order): bool {
            $this->assertSame((float) $order->grand_total, (float) ($order->subtotal + $order->shipping_cost));
            return true;
        })->andReturn([
            'transaction_reference' => 'test-payment-reference',
            'metadata' => ['snap_token' => 'fake-snap', 'client_key' => 'fake-client'],
        ]);
        $this->app->instance(PaymentGatewayInterface::class, $gateway);

        $forgedQuote = json_decode(Crypt::decryptString($quote['quote_token']), true, flags: JSON_THROW_ON_ERROR);
        $forgedQuote['cost'] = 0;
        $this->actingAs($user)->postJson(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => Crypt::encryptString(json_encode($forgedQuote, JSON_THROW_ON_ERROR)),
        ])->assertStatus(422)->assertJsonValidationErrors('shipping_quote_token');
        $this->assertSame(0, Order::count());
        $this->assertSame(10, $variant->fresh()->stock_qty);

        $expiredQuote = json_decode(Crypt::decryptString($quote['quote_token']), true, flags: JSON_THROW_ON_ERROR);
        $expiredQuote['issued_at'] = now()->subMinutes(16)->timestamp;
        $expiredQuote['expires_at'] = $expiredQuote['issued_at'] + 15 * 60;
        $this->postJson(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => Crypt::encryptString(json_encode($expiredQuote, JSON_THROW_ON_ERROR)),
        ])->assertStatus(422)->assertJsonValidationErrors('shipping_quote_token');
        $this->assertSame(0, Order::count());

        $response = $this->actingAs($user)->postJson(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => $quote['quote_token'],
            'shipping_cost' => 1,
        ])->assertOk();

        $order = \App\Models\Order::with('payments')->findOrFail($response->json('order_id'));
        $this->assertSame(16000.0, (float) $order->shipping_cost);
        $this->assertSame((float) ($order->subtotal + 16000), (float) $order->grand_total);
        $this->assertSame((float) $order->grand_total, (float) $order->payments->first()->amount);
        $this->assertSame(8, $variant->fresh()->stock_qty);
        $this->assertSame(700, $order->shipment->weight_gram);
    }

    public function test_checkout_rejects_changed_api_tariff_without_reserving_stock(): void
    {
        $user = User::factory()->customer()->create();
        $address = $this->customerAddress($user);
        $variant = $this->variant(350);
        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 2, 'unit_price' => $variant->finalPrice()]);
        $costCall = 0;
        Http::fake(function (Request $request) use (&$costCall) {
            if (str_contains($request->url(), '/destination/domestic-destination')) {
                $search = (string) $request->data()['search'];
                $destination = $search === 'Maguwoharjo'
                    ? $this->origin()
                    : $this->destination(17620, 'MENTENG', 'MENTENG', 'JAKARTA PUSAT', 'DKI JAKARTA', '10310');
                return Http::response($this->destinationResponse([$destination]), 200);
            }
            if (str_ends_with($request->url(), '/calculate/domestic-cost')) {
                $costCall++;
                $cost = $costCall === 1 ? 16000 : 17000;
                return Http::response(['meta' => ['code' => 200, 'status' => 'success'], 'data' => [
                    ['name' => 'JNE', 'code' => 'jne', 'service' => 'REG', 'description' => 'Layanan Reguler', 'cost' => $cost, 'etd' => '2 day'],
                ]], 200);
            }
            return Http::response([], 404);
        });

        $quote = $this->actingAs($user)->postJson(route('checkout.shipping.quotes'), [
            'address_id' => $address->id,
            'destination_id' => 17620,
            'courier' => 'jne',
        ])->assertOk()->json('quotes.0');
        $this->postJson(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => $quote['quote_token'],
        ])->assertStatus(422)->assertJsonValidationErrors('shipping_quote_token');

        $this->assertSame(0, \App\Models\Order::count());
        $this->assertSame(10, $variant->fresh()->stock_qty);
    }

    public function test_checkout_rejects_cart_contents_changed_without_weight_change(): void
    {
        $user = User::factory()->customer()->create();
        $address = $this->customerAddress($user);
        $variant = $this->variant(350);
        $replacement = $this->variant(350);
        $cart = Cart::create(['user_id' => $user->id]);
        $item = $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 2, 'unit_price' => $variant->finalPrice()]);

        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/destination/domestic-destination')) {
                $search = (string) $request->data()['search'];
                return Http::response($this->destinationResponse([$search === 'Maguwoharjo' ? $this->origin() : $this->destination(17620, 'MENTENG', 'MENTENG', 'JAKARTA PUSAT', 'DKI JAKARTA', '10310')]), 200);
            }
            if (str_ends_with($request->url(), '/calculate/domestic-cost')) {
                return Http::response(['meta' => ['code' => 200, 'status' => 'success'], 'data' => [
                    ['name' => 'JNE', 'code' => 'jne', 'service' => 'REG', 'description' => 'Layanan Reguler', 'cost' => 16000, 'etd' => '2 day'],
                ]], 200);
            }
            return Http::response([], 404);
        });

        $quote = $this->actingAs($user)->postJson(route('checkout.shipping.quotes'), [
            'address_id' => $address->id,
            'destination_id' => 17620,
            'courier' => 'jne',
        ])->assertOk()->json('quotes.0');

        $item->update(['product_variant_id' => $replacement->id]);
        $this->postJson(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => $quote['quote_token'],
        ])->assertStatus(422)->assertJsonValidationErrors('shipping_quote_token');

        $this->assertSame(0, Order::count());
        $this->assertSame(10, $variant->fresh()->stock_qty);
        $this->assertSame(10, $replacement->fresh()->stock_qty);

        $item->update(['product_variant_id' => $variant->id]);
        $address->update(['address_line' => 'Alamat diperbarui setelah quote dibuat']);
        $this->postJson(route('checkout.store'), [
            'address_id' => $address->id,
            'shipping_quote_token' => $quote['quote_token'],
        ])->assertStatus(422)->assertJsonValidationErrors('shipping_quote_token');
        $this->assertSame(0, Order::count());
    }

    public function test_voucher_preview_uses_api_quote_and_server_calculated_total(): void
    {
        $user = User::factory()->customer()->create();
        $address = $this->customerAddress($user);
        $variant = $this->variant(350);
        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 2, 'unit_price' => 50000]);
        Voucher::create(['code' => 'API10', 'type' => 'fixed', 'value' => 10000, 'is_active' => true]);
        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/destination/domestic-destination')) {
                $search = (string) $request->data()['search'];
                $destination = $search === 'Maguwoharjo'
                    ? $this->origin()
                    : $this->destination(17620, 'MENTENG', 'MENTENG', 'JAKARTA PUSAT', 'DKI JAKARTA', '10310');
                return Http::response($this->destinationResponse([$destination]), 200);
            }
            if (str_ends_with($request->url(), '/calculate/domestic-cost')) {
                return Http::response(['meta' => ['code' => 200, 'status' => 'success'], 'data' => [
                    ['name' => 'JNE', 'code' => 'jne', 'service' => 'REG', 'description' => 'Layanan Reguler', 'cost' => 16000, 'etd' => '2 day'],
                ]], 200);
            }
            return Http::response([], 404);
        });

        $quote = $this->actingAs($user)->postJson(route('checkout.shipping.quotes'), [
            'address_id' => $address->id,
            'destination_id' => 17620,
            'courier' => 'jne',
        ])->assertOk()->json('quotes.0');
        $this->postJson(route('checkout.voucher'), [
            'address_id' => $address->id,
            'voucher_code' => 'API10',
            'shipping_quote_token' => $quote['quote_token'],
        ])->assertOk()->assertJsonPath('discount', 10000)->assertJsonPath('total', 106000);
    }

    private function customerAddress(?User $user = null): Address
    {
        $user ??= User::factory()->customer()->create();
        return Address::factory()->create([
            'user_id' => $user->id,
            'village' => 'Menteng',
            'district' => 'Kecamatan Menteng',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'postal_code' => '10310',
        ]);
    }

    private function variant(int $weight): ProductVariant
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['category_id' => $category->id, 'base_price' => 50000, 'is_active' => true]);
        return ProductVariant::factory()->create(['product_id' => $product->id, 'stock_qty' => 10, 'weight_gram' => $weight]);
    }

    private function origin(): array
    {
        return $this->destination(31592, 'MAGUWOHARJO', 'DEPOK', 'SLEMAN', 'DI YOGYAKARTA', '55282');
    }

    private function destination(int $id, string $subdistrict, string $district, string $city, string $province, string $zip): array
    {
        return [
            'id' => $id,
            'label' => "$subdistrict, $district, $city, $province, $zip",
            'province_name' => $province,
            'city_name' => $city,
            'district_name' => $district,
            'subdistrict_name' => $subdistrict,
            'zip_code' => $zip,
        ];
    }

    private function destinationResponse(array $data): array
    {
        return ['meta' => ['code' => 200, 'status' => 'success', 'message' => 'Success'], 'data' => $data];
    }
}
