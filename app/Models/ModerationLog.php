<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModerationLog extends Model
{
    protected $fillable = [
        'moderatable_type',
        'moderatable_id',
        'performed_by',
        'action',
        'previous_status',
        'new_status',
        'reason',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function moderatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
