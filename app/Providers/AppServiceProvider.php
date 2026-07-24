<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\Category;
use App\Models\Neighborhood;
use App\Models\Post;
use App\Models\Promotion;
use App\Models\User;
use App\Observers\BusinessObserver;
use App\Observers\CategoryObserver;
use App\Observers\NeighborhoodObserver;
use App\Observers\PostObserver;
use App\Observers\PromotionObserver;
use App\Observers\UserObserver;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use NotificationChannels\WebPush\Events\NotificationFailed as WebPushFailed;

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
        Route::bind('neighborhood', function (string $value, \Illuminate\Routing\Route $route): Neighborhood {
            return Neighborhood::query()
                ->where('state_code', strtoupper((string) $route->parameter('state')))
                ->where('city_slug', $route->parameter('city'))
                ->where('slug', $value)
                ->firstOrFail();
        });

        Post::observe(PostObserver::class);
        Business::observe(BusinessObserver::class);
        Promotion::observe(PromotionObserver::class);
        Category::observe(CategoryObserver::class);
        User::observe(UserObserver::class);
        Neighborhood::observe(NeighborhoodObserver::class);

        Event::listen(NotificationFailed::class, function (NotificationFailed $event): void {
            if (method_exists($event->notification, 'releaseDeliveryReservation')) {
                $event->notification->releaseDeliveryReservation($event->notifiable, $event->channel);

                Log::warning('Notification delivery failed', [
                    'notification' => $event->notification::class,
                    'notification_id' => $event->notification->id,
                    'channel' => $event->channel,
                    'user_id' => $event->notifiable->getKey(),
                ]);
            }
        });

        View::composer('layouts.navigation', function (\Illuminate\View\View $view): void {
            $view->with('navigationNeighborhoods', Cache::remember(
                'neighborhoods:active',
                300,
                fn () => Neighborhood::query()->active()->orderBy('sort_order')->orderBy('id')->get(),
            ));
        });

        Event::listen(WebPushFailed::class, function (WebPushFailed $event): void {
            Log::warning('Web Push provider rejected a delivery', [
                'user_id' => $event->subscription->subscribable_id,
                'expired' => $event->report->isSubscriptionExpired(),
                'reason' => $event->report->getReason(),
            ]);
        });
    }
}
