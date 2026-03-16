<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClaimBusinessController extends Controller
{
    public function request(Business $business): RedirectResponse
    {
        return redirect()->route('businesses.show', $business);
    }

    public function verify(string $token): View|RedirectResponse
    {
        $business = Business::where('claim_token', $token)->first();

        if (! $business) {
            return redirect()->route('home')->with('error', 'Token inválido ou expirado.');
        }

        return view('businesses.claim', compact('business'));
    }
}
