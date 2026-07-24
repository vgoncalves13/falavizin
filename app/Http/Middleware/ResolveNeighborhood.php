<?php

namespace App\Http\Middleware;

use App\Models\Neighborhood;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveNeighborhood
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Neighborhood $neighborhood */
        $neighborhood = $request->route('neighborhood');

        if (! $neighborhood instanceof Neighborhood) {
            $route = $request->route();
            $neighborhood = Neighborhood::query()
                ->where('state_code', strtoupper((string) $route->parameter('state')))
                ->where('city_slug', $route->parameter('city'))
                ->where('slug', $neighborhood)
                ->firstOrFail();
            $route->setParameter('neighborhood', $neighborhood);
        }

        View::share('currentNeighborhood', $neighborhood);
        session()->put('current_neighborhood_id', $neighborhood->getKey());
        Cookie::queue('last_neighborhood_id', (string) $neighborhood->getKey(), 525_600);

        return $next($request);
    }
}
