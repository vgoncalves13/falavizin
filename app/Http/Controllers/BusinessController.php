<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function index(): View
    {
        return view('businesses.index');
    }

    public function show(Business $business): View
    {
        return view('businesses.show', compact('business'));
    }

    public function create(): View
    {
        return view('businesses.create');
    }

    public function store(): RedirectResponse
    {
        return redirect()->route('businesses.index');
    }

    public function edit(Business $business): View
    {
        return view('businesses.edit', compact('business'));
    }

    public function update(Business $business): RedirectResponse
    {
        return redirect()->route('businesses.show', $business);
    }
}
