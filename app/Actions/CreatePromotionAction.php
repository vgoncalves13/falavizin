<?php

namespace App\Actions;

use App\Enums\BusinessPlan;
use App\Models\Business;
use App\Models\Promotion;
use App\Models\User;
use App\Notifications\NewContentNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class CreatePromotionAction
{
    public function execute(Business $business, array $data): Promotion
    {
        $promotion = DB::transaction(function () use ($business, $data): Promotion {
            $lockedBusiness = Business::query()
                ->lockForUpdate()
                ->findOrFail($business->getKey());

            $this->ensurePromotionCanBeCreated($lockedBusiness);

            return $lockedBusiness->promotions()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'is_active' => true,
                'status' => 'pending',
            ]);
        });

        $this->notifyAdmins($promotion->title);

        return $promotion;
    }

    private function ensurePromotionCanBeCreated(Business $business): void
    {
        if (! $business->acceptsCommunityInteractions()) {
            throw ValidationException::withMessages([
                'title' => 'Este bairro não está mais ativo. Não é possível criar promoções.',
            ]);
        }

        if ($business->plan === BusinessPlan::Featured) {
            return;
        }

        $lastPromotion = $business->promotions()
            ->withTrashed()
            ->latest()
            ->first();

        if (! $lastPromotion) {
            return;
        }

        $nextAllowedAt = $lastPromotion->created_at->copy()->addDays(7);

        if (now()->lt($nextAllowedAt)) {
            throw ValidationException::withMessages([
                'title' => 'Este negócio já criou uma promoção recentemente. Próxima disponível em '
                    .$nextAllowedAt->format('d/m/Y \à\s H:i').'.',
            ]);
        }
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
