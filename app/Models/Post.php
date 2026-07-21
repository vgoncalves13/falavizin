<?php

namespace App\Models;

use App\Enums\PostResolutionStatus;
use App\Enums\PostStatus;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'service_category_id',
        'title',
        'slug',
        'body',
        'location',
        'image',
        'event_starts_at',
        'event_ends_at',
        'is_sponsored',
        'status',
        'approved_at',
        'reported_at',
        'reported_reason',
        'resolution_status',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'approved_at' => 'datetime',
            'reported_at' => 'datetime',
            'event_starts_at' => 'datetime',
            'event_ends_at' => 'datetime',
            'is_sponsored' => 'boolean',
            'resolution_status' => PostResolutionStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'service_category_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(Vote::class, 'votable');
    }

    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class);
    }

    public function saves(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_user_saves')->withPivot('created_at');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Approved);
    }

    public function scopeUpcomingEvents(Builder $query): Builder
    {
        return $query->approved()
            ->whereHas('category', fn ($q) => $q->where('slug', 'evento'))
            ->where('event_starts_at', '>=', now());
    }
}
