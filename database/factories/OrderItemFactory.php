<?php

namespace Database\Factories;

use App\Enums\ChamferType;
use App\Enums\SlotType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
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
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 20),
            'backing_plate_thickness' => fake()->optional(0.6)->randomFloat(2, 2, 6),
            'has_shim' => fake()->boolean(30),
            'color' => fake()->randomElement(['black', 'grey', 'silver', 'red', 'copper', 'gold']),
            'slot_type' => fake()->randomElement(SlotType::cases()),
            'chamfer_type' => fake()->randomElement(ChamferType::cases()),
        ];
    }
}
