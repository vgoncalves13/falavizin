<?php

namespace Database\Factories;

use App\Enums\PointEventReason;
use App\Models\PointEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PointEvent>
 */
class PointEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reason = fake()->randomElement(PointEventReason::cases());

        return [
            'user_id' => User::factory(),
            'points' => $reason->points(),
            'reason' => $reason,
        ];
    }
}
