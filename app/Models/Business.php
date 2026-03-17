<?php

namespace App\Models;

use App\Enums\BusinessPlan;
use App\Enums\BusinessStatus;
use Database\Factories\BusinessFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Business extends Model
{
    /** @use HasFactory<BusinessFactory> */
    use HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'slug',
        'description',
        'phone',
        'whatsapp',
        'address',
        'neighborhood',
        'city',
        'opening_hours',
        'website',
        'lat',
        'lng',
        'google_place_id',
        'plan',
        'status',
        'claimed',
        'claim_token',
        'claimed_at',
        'reported_at',
        'reported_reason',
    ];

    protected function casts(): array
    {
        return [
            'plan' => BusinessPlan::class,
            'status' => BusinessStatus::class,
            'opening_hours' => 'array',
            'claimed' => 'boolean',
            'claimed_at' => 'datetime',
            'reported_at' => 'datetime',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
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

    public function photos(): HasMany
    {
        return $this->hasMany(BusinessPhoto::class);
    }

    public function coverPhoto(): HasOne
    {
        return $this->hasOne(BusinessPhoto::class)->where('is_cover', true);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('plan', BusinessPlan::Featured)->where('status', BusinessStatus::Approved);
    }

    public function scopeInNeighborhood(Builder $query, string $neighborhood): Builder
    {
        return $query->where('neighborhood', 'like', "%{$neighborhood}%");
    }
}
