<?php

namespace App\Services\Import;

use App\Models\ImportRun;

class RequestBudget
{
    public function __construct(
        private readonly ImportRun $importRun,
    ) {}

    public function canMakeRequest(): bool
    {
        return $this->importRun->requests_made < $this->importRun->requests_budget;
    }

    public function remaining(): int
    {
        return max(0, $this->importRun->requests_budget - $this->importRun->requests_made);
    }

    public function consume(int $count = 1): void
    {
        $this->importRun->incrementRequests($count);
    }

    public function exhausted(): bool
    {
        return $this->importRun->budgetExhausted();
    }
}
