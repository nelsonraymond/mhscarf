<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_addresses(): void
    {
        $response = $this->get(route('addresses.index'));
        $response->assertRedirect(route('login'));

        $createResponse = $this->get(route('addresses.create'));
        $createResponse->assertRedirect(route('login'));
    }

    public function test_customer_can_create_address(): void
    {
        $customer = User::factory()->customer()->create();

        $addressData = [
            'label' => 'Rumah',
            'recipient_name' => 'Siti Aisyah',
            'phone' => '08990991531',
            'address_line' => 'Jl. Mawar No. 10',
            'village' => 'Cihapit',
            'district' => 'Bandung Wetan',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40114',
            'notes' => 'Pagar putih',
            'is_default' => 1,
        ];

        $response = $this->actingAs($customer)->post(route('addresses.store'), $addressData);

        $response->assertRedirect(route('addresses.index'));
        $this->assertDatabaseHas('addresses', [
            'user_id' => $customer->id,
            'recipient_name' => 'Siti Aisyah',
            'city' => 'Bandung',
            'is_default' => 1,
        ]);
    }

    public function test_customer_can_update_own_address(): void
    {
        $customer = User::factory()->customer()->create();
        $address = Address::factory()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Nama Lama',
            'city' => 'Bandung',
        ]);

        $response = $this->actingAs($customer)->put(route('addresses.update', $address), [
            'recipient_name' => 'Nama Baru',
            'phone' => '08987654321',
            'address_line' => 'Jl. Melati No. 5',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12110',
        ]);

        $response->assertRedirect(route('addresses.index'));
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'recipient_name' => 'Nama Baru',
            'city' => 'Jakarta Selatan',
        ]);
    }

    public function test_customer_cannot_edit_another_users_address(): void
    {
        $user1 = User::factory()->customer()->create();
        $user2 = User::factory()->customer()->create();

        $address1 = Address::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->get(route('addresses.edit', $address1));
        $response->assertForbidden();

        $updateResponse = $this->actingAs($user2)->put(route('addresses.update', $address1), [
            'recipient_name' => 'Hacker Name',
            'phone' => '08990991531',
            'address_line' => 'Jl. Ilegal No. 1',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'postal_code' => '60111',
        ]);
        $updateResponse->assertForbidden();

        $deleteResponse = $this->actingAs($user2)->delete(route('addresses.destroy', $address1));
        $deleteResponse->assertForbidden();
    }

    public function test_only_one_default_address_per_customer(): void
    {
        $customer = User::factory()->customer()->create();

        $addr1 = Address::factory()->create([
            'user_id' => $customer->id,
            'is_default' => true,
        ]);

        $addr2 = Address::factory()->create([
            'user_id' => $customer->id,
            'is_default' => false,
        ]);

        $this->assertTrue($addr1->fresh()->is_default);
        $this->assertFalse($addr2->fresh()->is_default);

        // Setting addr2 as default via setDefault endpoint
        $response = $this->actingAs($customer)->post(route('addresses.default', $addr2));
        $response->assertRedirect();

        $this->assertFalse($addr1->fresh()->is_default);
        $this->assertTrue($addr2->fresh()->is_default);
    }

    public function test_first_address_is_automatically_default(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)->post(route('addresses.store'), [
            'recipient_name' => 'Fatimah',
            'phone' => '08123456789',
            'address_line' => 'Jl. Anggrek No. 1',
            'city' => 'Yogyakarta',
            'province' => 'DIY',
            'postal_code' => '55281',
            'is_default' => 0, // Even if 0 sent, first address should default to true
        ]);

        $address = $customer->addresses()->first();
        $this->assertNotNull($address);
        $this->assertTrue($address->is_default);
    }
}
