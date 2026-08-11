<?php

namespace Database\Factories;

use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'brand' => fake()->company(),
            'priority' => fake()->randomElement(OrderPriority::cases()),
            'status' => fake()->randomElement(OrderStatus::cases()),
            'payment_notes' => fake()->boolean(40) ? fake()->sentence() : null,
            'ordered_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
