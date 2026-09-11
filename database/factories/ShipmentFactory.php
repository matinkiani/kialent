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
            'driver_name' => fake()->name(),
            'vehicle_plate' => fake()->optional()->numerify('???-##-###'),
            'destination' => fake()->optional()->address(),
            'total_master_boxes' => fake()->numberBetween(1, 100),
            'total_weight' => fake()->randomFloat(3, 0, 1000),
            'shipped_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
