<?php

namespace App\Livewire\Notifications;

use Livewire\Component;

class NotificationBell extends Component
{
    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function markRead(string $id): void
    {
        auth()->user()->notifications()->where('id', $id)->update(['read_at' => now()]);
    }

    public function render()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->limit(10)
            ->get();

        $unreadCount = auth()->user()->unreadNotifications()->count();

        return view('livewire.notifications.notification-bell', compact('notifications', 'unreadCount'));
    }
}
