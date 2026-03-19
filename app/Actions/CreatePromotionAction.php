<?php

namespace App\Actions;

use App\Models\Business;
use App\Models\Promotion;
use App\Models\User;
use App\Notifications\NewContentNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class CreatePromotionAction
{
    public function execute(Business $business, array $data): Promotion
    {
        $promotion = $business->promotions()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'is_active' => true,
            'status' => 'pending',
        ]);

        $this->notifyAdmins($promotion->title);

        return $promotion;
    }

    private function notifyAdmins(string $title): void
    {
        $admins = User::where('is_admin', true)->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new NewContentNotification('promotion', $title));
        Cache::forget('admin:moderation_count');
    }
}
