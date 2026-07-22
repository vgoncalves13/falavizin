<?php

namespace App\Actions;

use App\Models\ModerationLog;
use App\Models\Post;
use Carbon\Carbon;

class ToggleSponsorAction
{
    public function execute(Post $post, ?int $days = null): Post
    {
        $wasSponsored = $post->is_sponsored;

        if ($wasSponsored) {
            $post->update([
                'is_sponsored' => false,
                'sponsored_until' => null,
            ]);

            ModerationLog::create([
                'moderatable_type' => Post::class,
                'moderatable_id' => $post->id,
                'performed_by' => auth()->id(),
                'action' => 'sponsor_removed',
                'previous_status' => 'sponsored',
                'new_status' => 'not_sponsored',
                'reason' => null,
            ]);
        } else {
            $sponsoredUntil = $days ? Carbon::now()->addDays($days) : null;

            $post->update([
                'is_sponsored' => true,
                'sponsored_until' => $sponsoredUntil,
            ]);

            ModerationLog::create([
                'moderatable_type' => Post::class,
                'moderatable_id' => $post->id,
                'performed_by' => auth()->id(),
                'action' => 'sponsored',
                'previous_status' => 'not_sponsored',
                'new_status' => 'sponsored',
                'reason' => $days ? "Patrocinado por {$days} dias" : 'Patrocinado sem prazo',
            ]);
        }

        return $post->fresh();
    }
}
