<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\BusinessPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessPhoto>
 */
class BusinessPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'path' => 'photos/'.fake()->uuid().'.jpg',
            'is_cover' => false,
            'sort_order' => 0,
        ];
    }
}
