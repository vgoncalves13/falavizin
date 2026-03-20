<?php

namespace Database\Factories;

use App\Models\Poll;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Poll>
 */
class PollFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'question' => fake()->sentence().'?',
            'ends_at' => fake()->optional()->dateTimeBetween('now', '+1 month'),
        ];
    }
}
