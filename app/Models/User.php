<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Notifications\QueuedResetPassword;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    public const PUSH_NOTIFICATION_TYPES = [
        'comment',
        'comment_vote',
        'post_vote',
        'moderation',
        'plan_upgrade',
    ];

    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPushSubscriptions, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'neighborhood',
        'neighborhood_id',
        'is_admin',
        'role',
        'points',
        'notification_preferences',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'role' => UserRole::class,
            'points' => 'integer',
            'notification_preferences' => 'array',
        ];
    }

    public function isAdministrator(): bool
    {
        return $this->is_admin === true;
    }

    public function isModerator(): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        return $this->role?->canModerate() ?? false;
    }

    public function wantsEmailNotification(string $type): bool
    {
        $preferences = $this->notification_preferences ?? [];

        return $preferences[$type] ?? true;
    }

    public function wantsPushNotification(string $type): bool
    {
        $preferences = $this->notification_preferences ?? [];

        return $preferences['push'][$type] ?? false;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new QueuedResetPassword($token));
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    public function primaryNeighborhood(): BelongsTo
    {
        return $this->belongsTo(Neighborhood::class, 'neighborhood_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_user_favorites')->withPivot('created_at');
    }

    public function savedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_user_saves')->withPivot('created_at');
    }

    /**
     * @return BelongsToMany<Post>
     */
    public function interestedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_interests')->withPivot('message', 'created_at')->withTimestamps();
    }

    public function pointEvents(): HasMany
    {
        return $this->hasMany(PointEvent::class);
    }

    /**
     * @return HasMany<SocialAccount>
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }
}
