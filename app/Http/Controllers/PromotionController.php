<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        return view('promotions.index');
    }

    public function store(Business $business): RedirectResponse
    {
        return redirect()->route('businesses.show', $business);
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        return redirect()->back();
    }
}
