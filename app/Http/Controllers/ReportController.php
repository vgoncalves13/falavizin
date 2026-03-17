<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Post;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function post(Request $request, Post $post): RedirectResponse
    {
        if ($post->reported_at) {
            return redirect()->back()->with('info', 'Este post já foi reportado.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $post->update([
            'reported_at' => now(),
            'reported_reason' => $validated['reason'],
        ]);

        return redirect()->back()->with('success', 'Post reportado. Nossa equipe irá analisar em breve.');
    }

    public function business(Request $request, Business $business): RedirectResponse
    {
        if ($business->reported_at) {
            return redirect()->back()->with('info', 'Este negócio já foi reportado.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $business->update([
            'reported_at' => now(),
            'reported_reason' => $validated['reason'],
        ]);

        return redirect()->back()->with('success', 'Negócio reportado. Nossa equipe irá analisar em breve.');
    }

    public function promotion(Request $request, Promotion $promotion): RedirectResponse
    {
        if ($promotion->reported_at) {
            return redirect()->back()->with('info', 'Esta promoção já foi reportada.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $promotion->update([
            'reported_at' => now(),
            'reported_reason' => $validated['reason'],
        ]);

        return redirect()->back()->with('success', 'Promoção reportada. Nossa equipe irá analisar em breve.');
    }
}
