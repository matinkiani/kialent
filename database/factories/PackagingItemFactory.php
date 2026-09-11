<?php

namespace Database\Factories;

use App\Models\Packaging;
use App\Models\PackagingItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PackagingItem>
 */
class PackagingItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'packaging_id' => Packaging::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 20),
        ];
    }
}
