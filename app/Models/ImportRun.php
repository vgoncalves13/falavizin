<?php

namespace App\Models;

use App\Enums\ImportRunStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRun extends Model
{
    protected $fillable = [
        'neighborhood_id',
        'status',
        'mode',
        'config',
        'stats',
        'cells',
        'seen_place_ids',
        'requests_made',
        'requests_budget',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ImportRunStatus::class,
            'config' => 'array',
            'stats' => 'array',
            'cells' => 'array',
            'seen_place_ids' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function neighborhood(): BelongsTo
    {
        return $this->belongsTo(Neighborhood::class);
    }

    public function pendingCells(): array
    {
        return array_values(array_filter(
            $this->cells ?? [],
            fn (array $cell) => ($cell['status'] ?? 'pending') === 'pending'
        ));
    }

    public function nextPendingCellIndex(): ?int
    {
        foreach ($this->cells ?? [] as $index => $cell) {
            if (($cell['status'] ?? 'pending') === 'pending') {
                return $index;
            }
        }

        return null;
    }

    public function markCellProcessed(int $index, array $result): void
    {
        $cells = $this->cells ?? [];
        if (isset($cells[$index])) {
            $cells[$index]['status'] = 'processed';
            $cells[$index]['result'] = $result;
            $this->cells = $cells;
            $this->saveQuietly();
        }
    }

    public function markCellSaturated(int $index): void
    {
        $cells = $this->cells ?? [];
        if (isset($cells[$index])) {
            $cells[$index]['status'] = 'saturated';
            $this->cells = $cells;
            $this->saveQuietly();
        }
    }

    public function addCells(array $newCells): void
    {
        $cells = $this->cells ?? [];
        foreach ($newCells as $cell) {
            $cell['status'] = 'pending';
            $cells[] = $cell;
        }
        $this->cells = $cells;
        $this->saveQuietly();
    }

    public function incrementRequests(int $count = 1): void
    {
        $this->requests_made += $count;
        $this->saveQuietly();
    }

    public function budgetExhausted(): bool
    {
        return $this->requests_made >= $this->requests_budget;
    }

    public function markRunning(): void
    {
        $this->update([
            'status' => ImportRunStatus::Running,
            'started_at' => now(),
        ]);
    }

    public function markCompleted(array $stats): void
    {
        $this->update([
            'status' => ImportRunStatus::Completed,
            'stats' => $stats,
            'finished_at' => now(),
        ]);
    }

    public function markFailed(string $message): void
    {
        $this->update([
            'status' => ImportRunStatus::Failed,
            'error_message' => $message,
            'finished_at' => now(),
        ]);
    }

    public function markCancelled(): void
    {
        $this->update([
            'status' => ImportRunStatus::Cancelled,
            'finished_at' => now(),
        ]);
    }

    public function addSeenPlaceIds(array $ids): void
    {
        $seen = $this->seen_place_ids ?? [];
        $this->seen_place_ids = array_unique(array_merge($seen, $ids));
        $this->saveQuietly();
    }

    public function hasSeen(string $placeId): bool
    {
        return in_array($placeId, $this->seen_place_ids ?? [], true);
    }

    public function statsSnapshot(): array
    {
        $cells = $this->cells ?? [];

        return array_merge($this->stats ?? [], [
            'requests_made' => $this->requests_made,
            'requests_budget' => $this->requests_budget,
            'cells_total' => count($cells),
            'cells_pending' => count(array_filter($cells, fn ($c) => ($c['status'] ?? 'pending') === 'pending')),
            'cells_processed' => count(array_filter($cells, fn ($c) => ($c['status'] ?? '') === 'processed')),
            'cells_saturated' => count(array_filter($cells, fn ($c) => ($c['status'] ?? '') === 'saturated')),
        ]);
    }
}
