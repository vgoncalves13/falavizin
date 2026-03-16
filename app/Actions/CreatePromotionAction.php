<?php

namespace App\Actions;

use App\Models\Business;
use App\Models\Promotion;

class CreatePromotionAction
{
    public function execute(Business $business, array $data): Promotion
    {
        return $business->promotions()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'is_active' => true,
            'status' => 'approved',
        ]);
    }
}
