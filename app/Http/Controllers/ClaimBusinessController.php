<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use App\Notifications\NewContentNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class ClaimBusinessController extends Controller
{
    public function request(Business $business): RedirectResponse
    {
        $requested = Business::query()
            ->whereKey($business->getKey())
            ->where('claimed', false)
            ->whereNull('claim_user_id')
            ->update([
                'claim_user_id' => auth()->id(),
                'claim_requested_at' => now(),
            ]);

        if ($requested === 0) {
            $business->refresh();

            $message = match (true) {
                $business->claimed => 'Este negócio já foi reivindicado.',
                $business->claim_user_id === auth()->id() => 'Sua solicitação já está aguardando análise.',
                default => 'Este negócio já possui uma solicitação em análise.',
            };

            return redirect()->route('businesses.show', $business)->with('error', $message);
        }

        Notification::send(
            User::where('is_admin', true)->get(),
            new NewContentNotification('claim', $business->name),
        );
        Cache::forget('admin:moderation_count');

        return redirect()->route('businesses.show', $business)
            ->with('success', 'Solicitação enviada! Um administrador verificará os dados do negócio.');
    }
}
