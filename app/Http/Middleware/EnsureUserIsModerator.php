<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsModerator
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->user()?->isModerator()) {
            abort(403);
        }

        return $next($request);
    }
}
