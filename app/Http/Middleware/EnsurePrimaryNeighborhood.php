<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrimaryNeighborhood
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->primaryNeighborhood?->is_active) {
            return redirect()->route('neighborhoods.select');
        }

        return $next($request);
    }
}
