<?php

namespace App\Models;

use App\Enums\PointEventReason;
use Database\Factories\PointEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PointEvent extends Model
{
    /** @use HasFactory<PointEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'points',
        'reason',
        'pointable_type',
        'pointable_id',
    ];

    protected function casts(): array
    {
        return [
            'reason' => PointEventReason::class,
            'points' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pointable(): MorphTo
    {
        return $this->morphTo();
    }
}
