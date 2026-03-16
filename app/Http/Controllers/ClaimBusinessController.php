<?php

namespace App\Http\Controllers;

use App\Actions\ClaimBusinessAction;
use App\Mail\ClaimBusinessMail;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClaimBusinessController extends Controller
{
    public function request(Business $business): RedirectResponse
    {
        if ($business->claimed) {
            return redirect()->route('businesses.show', $business)
                ->with('error', 'Este negócio já foi reivindicado.');
        }

        $business->update(['claim_token' => Str::uuid()->toString()]);

        Mail::to(auth()->user()->email)->send(new ClaimBusinessMail($business));

        return redirect()->route('businesses.show', $business)
            ->with('success', 'Email enviado! Verifique sua caixa de entrada para confirmar a reivindicação.');
    }

    public function verify(string $token): View|RedirectResponse
    {
        $business = Business::where('claim_token', $token)->first();

        if (! $business) {
            return redirect()->route('home')->with('error', 'Token inválido ou expirado.');
        }

        (new ClaimBusinessAction)->execute($business, auth()->user());

        return redirect()->route('businesses.show', $business)
            ->with('success', 'Negócio reivindicado com sucesso! Agora você pode editar o perfil.');
    }
}
