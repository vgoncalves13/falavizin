<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Neighborhood;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LegacyNeighborhoodRedirectController extends Controller
{
    public function index(string $type): RedirectResponse
    {
        $neighborhood = $this->currentNeighborhood(request());

        $segments = match ($type) {
            'feed' => 'feed',
            'servicos' => 'servicos',
            default => $type,
        };

        return redirect()->to(
            "/{$neighborhood->state_code}/{$neighborhood->city_slug}/{$neighborhood->slug}/{$segments}",
            302,
        )->withInput(request()->query());
    }

    public function post(Post $post): RedirectResponse
    {
        return redirect()->to($post->canonicalUrl(), 301);
    }

    public function business(Business $business): RedirectResponse
    {
        return redirect()->to($business->canonicalUrl(), 301);
    }

    private function currentNeighborhood(Request $request): Neighborhood
    {
        $primary = $request->user()?->primaryNeighborhood;

        if ($primary?->is_active) {
            return $primary;
        }

        return Neighborhood::query()->active()->find($request->cookie('last_neighborhood_id'))
            ?? Neighborhood::query()->active()->orderBy('sort_order')->orderBy('id')->firstOrFail();
    }
}
