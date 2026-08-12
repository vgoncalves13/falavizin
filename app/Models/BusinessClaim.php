<?php

namespace App\Models;

use App\Enums\BusinessClaimStatus;
use Database\Factories\BusinessClaimFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessClaim extends Model
{
    /** @use HasFactory<BusinessClaimFactory> */
    use HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'status',
        'message',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => BusinessClaimStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', BusinessClaimStatus::Pending);
    }
}
