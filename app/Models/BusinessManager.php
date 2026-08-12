<?php

namespace App\Models;

use App\Enums\BusinessManagerRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessManager extends Model
{
    protected $fillable = [
        'business_id',
        'user_id',
        'role',
        'granted_by',
        'granted_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => BusinessManagerRole::class,
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
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

    public function grantor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
