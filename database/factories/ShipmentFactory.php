<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shipment>
 */
class ShipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'courier' => 'MH Scarf Express Delivery',
            'service' => 'Reguler',
            'tracking_number' => 'MTE'.fake()->numerify('##########'),
            'shipping_cost' => 15000,
            'status' => 'pending',
            'shipped_at' => null,
            'delivered_at' => null,
        ];
    }
}
