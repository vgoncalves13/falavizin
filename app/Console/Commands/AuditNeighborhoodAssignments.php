<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;

class AuditNeighborhoodAssignments extends Command
{
    protected $signature = 'neighborhoods:audit';

    protected $description = 'Audita atribuições de bairro em users, posts e businesses';

    public function handle(): int
    {
        $users = User::query()->whereNull('neighborhood_id')->count();
        $posts = Post::query()->withTrashed()->whereNull('neighborhood_id')->count();
        $businesses = Business::query()->withTrashed()->whereNull('neighborhood_id')->count();

        $this->line("users sem bairro: {$users}");
        $this->line("posts sem bairro: {$posts}");
        $this->line("businesses sem bairro: {$businesses}");

        return $posts === 0 && $businesses === 0 ? self::SUCCESS : self::FAILURE;
    }
}
