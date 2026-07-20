<?php

namespace App\Actions;

use App\Models\Promotion;

class UpdatePromotionAction
{
    public function execute(Promotion $promotion, array $data): Promotion
    {
        $promotion->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ]);

        return $promotion->fresh();
    }
}
