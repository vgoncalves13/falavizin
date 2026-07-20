<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\Category;
use App\Models\Post;
use App\Models\Promotion;
use App\Models\User;
use App\Services\HomeCache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([Post::class, Business::class, Promotion::class, Category::class, User::class] as $model) {
            $model::observe(HomeCache::class);
        }
    }
}
