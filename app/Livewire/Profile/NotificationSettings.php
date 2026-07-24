<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Livewire\Component;

class NotificationSettings extends Component
{
    public array $preferences = [];

    private const TYPES = [
        'comment' => [
            'label' => 'Comentários e respostas',
            'description' => 'Quando alguém comentar na sua publicação ou responder ao seu comentário.',
            'email' => false,
            'push' => true,
        ],
        'comment_vote' => [
            'label' => 'Reações nos meus comentários',
            'description' => 'Quando alguém reagir a um comentário seu.',
            'email' => false,
            'push' => true,
        ],
        'post_vote' => [
            'label' => 'Reações nas minhas publicações',
            'description' => 'Quando alguém reagir a uma publicação sua.',
            'email' => false,
            'push' => true,
        ],
        'moderation' => [
            'label' => 'Moderação do meu conteúdo',
            'description' => 'Decisões sobre publicações, negócios ou promoções enviados por você.',
            'email' => true,
            'push' => true,
        ],
        'new_content' => [
            'label' => 'Novo conteúdo para moderar',
            'description' => 'Avisos para administradores e moderadores.',
            'email' => true,
            'push' => false,
        ],
        'plan_upgrade' => [
            'label' => 'Atualizações de plano',
            'description' => 'Solicitações e aprovações de destaque do seu negócio.',
            'email' => true,
            'push' => true,
        ],
    ];

    public function mount(): void
    {
        $this->preferences = auth()->user()->notification_preferences ?? [];
    }

    public function togglePreference(string $channel, string $type): void
    {
        abort_unless(array_key_exists($type, self::TYPES), 422);
        abort_unless(in_array($channel, ['email', 'push'], true), 422);
        abort_unless(self::TYPES[$type][$channel], 422);

        if ($channel === 'push') {
            $pushPreferences = $this->preferences['push'] ?? [];
            $pushPreferences[$type] = ! ($pushPreferences[$type] ?? false);
            $this->preferences['push'] = $pushPreferences;
        } else {
            $this->preferences[$type] = ! ($this->preferences[$type] ?? true);
        }

        $this->savePreferences();
    }

    private function savePreferences(): void
    {
        auth()->user()->update([
            'notification_preferences' => $this->preferences,
        ]);

        $this->dispatch('preferences-saved');
    }

    public function render()
    {
        return view('livewire.profile.notification-settings', [
            'types' => self::TYPES,
            'hasSelectedPushTypes' => collect(User::PUSH_NOTIFICATION_TYPES)
                ->contains(fn (string $type): bool => $this->preferences['push'][$type] ?? false),
        ]);
    }
}
