<?php

namespace App\Livewire\Events;

use App\Models\Neighborhood;
use App\Models\Post;
use Carbon\Carbon;
use Livewire\Component;

class EventCalendar extends Component
{
    public Neighborhood $neighborhood;

    public string $currentMonth;

    public ?string $selectedDate = null;

    /** @var array<string, array<int, array{id: int, title: string, time: string, url: string}>> */
    public array $eventsByDate = [];

    public function mount(): void
    {
        $this->currentMonth = Carbon::now()->format('Y-m');
        $this->loadEvents();
    }

    public function updatedSelectedDate(?string $date): void
    {
        $this->selectedDate = $date;
    }

    public function previousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth)->subMonth()->format('Y-m');
        $this->selectedDate = null;
        $this->loadEvents();
    }

    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth)->addMonth()->format('Y-m');
        $this->selectedDate = null;
        $this->loadEvents();
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $this->selectedDate === $date ? null : $date;
    }

    private function loadEvents(): void
    {
        $start = Carbon::parse($this->currentMonth)->startOfMonth()->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();

        $events = Post::query()
            ->forNeighborhood($this->neighborhood)
            ->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'evento'))
            ->whereNotNull('event_starts_at')
            ->whereBetween('event_starts_at', [$start, $end])
            ->select('id', 'title', 'event_starts_at', 'slug')
            ->orderBy('event_starts_at')
            ->get();

        $this->eventsByDate = [];

        foreach ($events as $event) {
            $date = $event->event_starts_at->format('Y-m-d');
            $this->eventsByDate[$date][] = [
                'id' => $event->id,
                'title' => $event->title,
                'time' => $event->event_starts_at->format('H:i'),
                'url' => route('neighborhood.feed.show', [
                    ...$this->neighborhood->routeParameters(),
                    'post' => $event->slug,
                ]),
            ];
        }
    }

    public function render()
    {
        $month = Carbon::parse($this->currentMonth);
        $daysInMonth = $month->daysInMonth;
        $firstDayOfWeek = $month->copy()->startOfMonth()->dayOfWeek;
        $today = Carbon::now()->format('Y-m-d');

        $days = [];

        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $days[] = null;
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $month->copy()->day($day)->format('Y-m-d');
            $days[] = [
                'day' => $day,
                'date' => $date,
                'isToday' => $date === $today,
                'hasEvents' => isset($this->eventsByDate[$date]),
                'eventCount' => isset($this->eventsByDate[$date]) ? count($this->eventsByDate[$date]) : 0,
            ];
        }

        return view('livewire.events.event-calendar', [
            'month' => $month,
            'days' => $days,
            'selectedEvents' => $this->selectedDate && isset($this->eventsByDate[$this->selectedDate])
                ? $this->eventsByDate[$this->selectedDate]
                : [],
        ]);
    }
}
