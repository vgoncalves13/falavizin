<?php

namespace Database\Factories;

use App\Enums\BusinessClaimStatus;
use App\Models\Business;
use App\Models\BusinessClaim;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessClaim>
 */
class BusinessClaimFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'user_id' => User::factory(),
            'status' => BusinessClaimStatus::Pending,
            'message' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => BusinessClaimStatus::Pending]);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => BusinessClaimStatus::Approved,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(['status' => BusinessClaimStatus::Rejected]);
    }
}
