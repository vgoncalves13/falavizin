<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('-1 week', 'now');

        return [
            'business_id' => Business::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'starts_at' => $startsAt,
            'ends_at' => fake()->optional()->dateTimeBetween($startsAt, '+1 month'),
            'is_active' => true,
            'status' => 'approved',
        ];
    }
}
