<?php

namespace Database\Factories;

use App\Enums\BusinessPlan;
use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->company(),
            'description' => fake()->paragraph(),
            'phone' => fake()->phoneNumber(),
            'whatsapp' => fake()->optional()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'neighborhood' => fake()->citySuffix(),
            'city' => fake()->city(),
            'plan' => BusinessPlan::Free,
            'status' => BusinessStatus::Approved,
            'claimed' => false,
        ];
    }

    public function featured(): static
    {
        return $this->state(['plan' => BusinessPlan::Featured]);
    }

    public function claimed(): static
    {
        return $this->state([
            'claimed' => true,
            'claimed_at' => now(),
        ]);
    }
}
