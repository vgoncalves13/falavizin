<?php

namespace Database\Factories;

use App\Models\Neighborhood;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Neighborhood>
 */
class NeighborhoodFactory extends Factory
{
    protected $model = Neighborhood::class;

    public function definition(): array
    {
        return [
            'name' => fake()->citySuffix(),
            'slug' => fake()->unique()->slug(),
            'city' => 'Rio de Janeiro',
            'city_slug' => 'rio-de-janeiro',
            'state_code' => 'RJ',
            'latitude' => fake()->latitude(-23.0, -22.8),
            'longitude' => fake()->longitude(-43.5, -43.1),
            'is_active' => true,
            'sort_order' => fake()->unique()->numberBetween(1, 10_000),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
