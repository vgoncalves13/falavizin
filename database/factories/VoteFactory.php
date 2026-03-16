<?php

namespace Database\Factories;

use App\Enums\VoteType;
use App\Models\Post;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vote>
 */
class VoteFactory extends Factory
{
    public function definition(): array
    {
        $post = Post::factory()->create();

        return [
            'user_id' => User::factory(),
            'votable_type' => Post::class,
            'votable_id' => $post->id,
            'type' => fake()->randomElement(VoteType::cases()),
        ];
    }
}
