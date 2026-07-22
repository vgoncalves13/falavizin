<?php

namespace App\Livewire\Business;

use App\Models\Business;
use App\Models\BusinessAnalytics;
use Livewire\Component;

class AnalyticsDashboard extends Component
{
    public Business $business;

    public int $days = 30;

    public array $stats = [];

    public array $dailyStats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function setDays(int $days): void
    {
        $this->days = $days;
        $this->loadStats();
    }

    private function loadStats(): void
    {
        $this->stats = BusinessAnalytics::getStats($this->business, $this->days);
        $this->dailyStats = BusinessAnalytics::getDailyStats($this->business, $this->days);
    }

    public function render()
    {
        return view('livewire.business.analytics-dashboard');
    }
}
