<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class ExpireSponsoredPosts extends Command
{
    protected $signature = 'posts:expire-sponsored';

    protected $description = 'Expire sponsored posts that have passed their sponsored_until date';

    public function handle(): int
    {
        $expired = Post::query()
            ->where('is_sponsored', true)
            ->whereNotNull('sponsored_until')
            ->where('sponsored_until', '<', now())
            ->update([
                'is_sponsored' => false,
                'sponsored_until' => null,
            ]);

        $this->info("Expired {$expired} sponsored posts.");

        return self::SUCCESS;
    }
}
