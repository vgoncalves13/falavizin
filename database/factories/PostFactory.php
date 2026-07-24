<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Neighborhood;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'neighborhood_id' => Neighborhood::factory(),
            'title' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'location' => fake()->optional()->address(),
            'status' => PostStatus::Approved,
            'approved_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => PostStatus::Pending, 'approved_at' => null]);
    }
}
