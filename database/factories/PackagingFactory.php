<?php

namespace Database\Factories;

use App\Models\Packaging;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Packaging>
 */
class PackagingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'brand_name' => fake()->company(),
        ];
    }
}
