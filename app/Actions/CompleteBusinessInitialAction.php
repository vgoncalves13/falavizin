<?php

namespace App\Actions;

use App\Enums\BusinessOnboardingStep;
use App\Models\Business;
use App\Models\User;
use App\Services\BusinessOnboardingProgress;

class CompleteBusinessInitialAction
{
    /**
     * Registra a ação inicial para um negócio administrado pelo usuário.
     * Ações elegíveis: novidade, promoção, evento, compartilhamento, QR Code.
     */
    public function execute(Business $business, User $user, string $action, ?string $url = null): void
    {
        if (! $business->isManagedBy($user)) {
            return;
        }

        (new BusinessOnboardingProgress)->completeStep($business, BusinessOnboardingStep::InitialAction, $user, [
            'action' => $action,
            'url' => $url,
            'completed_at' => now()->toDateTimeString(),
        ]);
    }

    /** @return array<int, array{key: string, label: string}> */
    public static function eligibleActions(): array
    {
        return [
            ['key' => 'news', 'label' => 'Publicar uma novidade'],
            ['key' => 'promotion', 'label' => 'Publicar uma promoção'],
            ['key' => 'event', 'label' => 'Divulgar um evento'],
            ['key' => 'share', 'label' => 'Compartilhar o perfil'],
            ['key' => 'qr', 'label' => 'Solicitar QR Code para o estabelecimento'],
        ];
    }
}
