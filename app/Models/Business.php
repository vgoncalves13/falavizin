<?php

namespace App\Models;

use App\Enums\BusinessPlan;
use App\Enums\BusinessStatus;
use Database\Factories\BusinessFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'neighborhood_id',
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
        'claim_user_id',
        'claim_requested_at',
        'claimed_at',
        'plan_upgrade_requested_at',
        'reported_at',
        'reported_reason',
    ];

    protected function casts(): array
    {
        return [
            'plan' => BusinessPlan::class,
            'status' => BusinessStatus::class,
            'phone' => 'array',
            'opening_hours' => 'array',
            'claimed' => 'boolean',
            'claim_requested_at' => 'datetime',
            'claimed_at' => 'datetime',
            'plan_upgrade_requested_at' => 'datetime',
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

    public function claimUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claim_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function localNeighborhood(): BelongsTo
    {
        return $this->belongsTo(Neighborhood::class, 'neighborhood_id');
    }

    /**
     * @return BelongsToMany<Category>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'business_categories')->withTimestamps();
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

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_user_favorites')->withPivot('created_at');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(BusinessAnalytics::class);
    }

    public function averageRating(): ?float
    {
        $avg = $this->reviews()->avg('rating');

        return $avg ? round($avg, 1) : null;
    }

    public function positiveReviewsCount(): int
    {
        return $this->reviews()->where('rating', '>=', 4)->count();
    }

    /**
     * Returns true if the business is currently open, false if closed today,
     * or null if no opening hours are configured.
     */
    public function isOpenNow(): ?bool
    {
        if (empty($this->opening_hours)) {
            return null;
        }

        $dayNames = [
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
            7 => 'Domingo',
        ];

        $now = now();

        foreach ([$now, $now->copy()->subDay()] as $date) {
            $hours = collect($this->opening_hours)->firstWhere('day', $dayNames[$date->isoWeekday()]);

            if (! $hours || ($hours['closed'] ?? true) || empty($hours['open']) || empty($hours['close'])) {
                continue;
            }

            $opensAt = $date->copy()->startOfDay()->setTimeFromTimeString($hours['open']);
            $closesAt = $date->copy()->startOfDay()->setTimeFromTimeString($hours['close']);

            if ($closesAt->lte($opensAt)) {
                $closesAt->addDay();
            }

            if ($now->betweenIncluded($opensAt, $closesAt)) {
                return true;
            }
        }

        return false;
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('plan', BusinessPlan::Featured)->where('status', BusinessStatus::Approved);
    }

    public function scopeInNeighborhood(Builder $query, string $neighborhood): Builder
    {
        return $query->where('neighborhood', 'like', "%{$neighborhood}%");
    }

    public function scopeForNeighborhood(Builder $query, Neighborhood|int $neighborhood): Builder
    {
        return $query->where('neighborhood_id', $neighborhood instanceof Neighborhood
            ? $neighborhood->getKey()
            : $neighborhood);
    }

    public function acceptsCommunityInteractions(): bool
    {
        return (bool) $this->localNeighborhood?->is_active;
    }

    public function canonicalUrl(bool $absolute = true): string
    {
        return route('neighborhood.businesses.show', [
            ...$this->localNeighborhood->routeParameters(),
            'business' => $this,
        ], $absolute);
    }
}
