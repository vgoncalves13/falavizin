<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNeighborhoodIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->route('neighborhood')?->is_active, 404);

        return $next($request);
    }
}
