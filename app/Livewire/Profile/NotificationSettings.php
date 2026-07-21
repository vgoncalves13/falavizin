<?php

namespace App\Livewire\Profile;

use Livewire\Component;

class NotificationSettings extends Component
{
    public array $preferences = [];

    private const TYPES = [
        'comment' => 'Comentários nos meus posts',
        'comment_vote' => 'Curtidas nos meus comentários',
        'moderation' => 'Moderação de conteúdo',
        'new_content' => 'Novo conteúdo para moderar',
        'plan_upgrade' => 'Upgrades de plano',
    ];

    public function mount(): void
    {
        $this->preferences = auth()->user()->notification_preferences ?? [];
    }

    public function togglePreference(string $type): void
    {
        $this->preferences[$type] = ! ($this->preferences[$type] ?? true);
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
        ]);
    }
}
