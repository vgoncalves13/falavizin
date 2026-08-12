<?php

namespace App\Models;

use App\Enums\BusinessOnboardingStep as BusinessOnboardingStepEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessOnboardingStep extends Model
{
    protected $fillable = [
        'business_id',
        'step',
        'completed_at',
        'completed_by',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'step' => BusinessOnboardingStepEnum::class,
            'completed_at' => 'datetime',
            'data' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
