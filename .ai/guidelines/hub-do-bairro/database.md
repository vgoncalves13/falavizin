# Hub do Bairro — Banco de Dados

## Regra Crítica: NUNCA usar `$table->enum()` nas migrations

Sempre usar `$table->string()`. O controle de valores é feito via **Enum PHP + cast no Model + validação no Form Request**.

**Motivo:** `enum` no MySQL exige `ALTER TABLE` completo para adicionar valores em produção. `string` + enum PHP é mais flexível e igualmente seguro.

```php
// ❌ NUNCA
$table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');

// ✅ SEMPRE
$table->string('status')->default('approved'); // valores controlados via PostStatus enum PHP
```

---

## Diagrama de Entidades

```
users
  └─< posts (author)
  └─< comments (author)
  └─< votes (voter)
  └─< businesses (owner — nullable se não reivindicado)

posts
  └─< comments
  └─< votes
  └─ belongs_to category

businesses
  └─< promotions
  └─< business_photos
  └─ belongs_to category
  └─ belongs_to user (nullable — owner)

categories (compartilhada entre posts e businesses via type)

promotions
  └─ belongs_to business
```

---

## Migrations — Campos Principais

### `users`
```php
$table->id();
$table->string('name');
$table->string('email')->unique();
$table->string('password');
$table->string('phone')->nullable();
$table->string('neighborhood')->nullable();
$table->boolean('is_admin')->default(false);
$table->timestamps();
```

### `categories`
```php
$table->id();
$table->string('name');
$table->string('slug')->unique();
$table->string('icon')->nullable();      // nome do heroicon sem prefixo: 'bolt', 'home', etc.
$table->string('type')->default('both'); // 'post' | 'business' | 'both' — via CategoryType enum
$table->integer('sort_order')->default(0);
$table->timestamps();
```

### `posts`
```php
$table->id();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->foreignId('category_id')->constrained();
$table->string('title');
$table->text('body');
$table->string('location')->nullable();
$table->string('status')->default('approved'); // 'pending' | 'approved' | 'rejected' — via PostStatus enum
$table->timestamp('approved_at')->nullable();
$table->timestamps();
$table->softDeletes();

$table->index(['status', 'created_at']);
$table->index('category_id');
```

### `comments`
```php
$table->id();
$table->foreignId('post_id')->constrained()->cascadeOnDelete();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->text('body');
$table->string('status')->default('approved'); // 'approved' | 'rejected' — via enum PHP
$table->timestamps();
$table->softDeletes();
```

### `votes`
```php
$table->id();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->morphs('votable');
$table->string('type'); // 'helpful' | 'not_helpful' — via VoteType enum
$table->timestamps();

$table->unique(['user_id', 'votable_type', 'votable_id']);
```

### `businesses`
```php
$table->id();
$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
$table->foreignId('category_id')->constrained();
$table->string('name');
$table->string('slug')->unique();
$table->text('description')->nullable();
$table->string('phone')->nullable();
$table->string('whatsapp')->nullable();
$table->string('address')->nullable();
$table->string('neighborhood');
$table->string('city')->default('');
$table->json('opening_hours')->nullable(); // {"seg":"08:00-18:00","ter":"08:00-18:00"}
$table->string('website')->nullable();
$table->decimal('lat', 10, 8)->nullable();
$table->decimal('lng', 11, 8)->nullable();
$table->string('google_place_id')->nullable()->unique();
$table->string('plan')->default('free');       // 'free' | 'featured' — via BusinessPlan enum
$table->string('status')->default('approved'); // 'pending' | 'approved' | 'rejected' — via BusinessStatus enum
$table->boolean('claimed')->default(false);
$table->string('claim_token')->nullable();
$table->timestamp('claimed_at')->nullable();
$table->timestamps();
$table->softDeletes();

$table->index(['neighborhood', 'status']);
$table->index(['plan', 'status']);
$table->index('category_id');
```

### `business_photos`
```php
$table->id();
$table->foreignId('business_id')->constrained()->cascadeOnDelete();
$table->string('path');
$table->boolean('is_cover')->default(false);
$table->integer('sort_order')->default(0);
$table->timestamps();
```

### `promotions`
```php
$table->id();
$table->foreignId('business_id')->constrained()->cascadeOnDelete();
$table->string('title');
$table->text('description')->nullable();
$table->date('starts_at')->nullable();
$table->date('ends_at')->nullable();
$table->boolean('is_active')->default(true);
$table->string('status')->default('approved'); // 'pending' | 'approved' | 'rejected' — via enum PHP
$table->timestamps();
$table->softDeletes();

$table->index(['is_active', 'ends_at', 'status']);
```

---

## Enums PHP (app/Enums/)

```php
// PostStatus.php
enum PostStatus: string {
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}

// BusinessPlan.php
enum BusinessPlan: string {
    case Free     = 'free';
    case Featured = 'featured';
}

// BusinessStatus.php
enum BusinessStatus: string {
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}

// CategoryType.php
enum CategoryType: string {
    case Post     = 'post';
    case Business = 'business';
    case Both     = 'both';
}

// VoteType.php
enum VoteType: string {
    case Helpful    = 'helpful';
    case NotHelpful = 'not_helpful';
}
```

---

## Casts nos Models — Obrigatório

```php
// Post
protected $casts = [
    'status' => PostStatus::class,
];

// Business
protected $casts = [
    'plan'          => BusinessPlan::class,
    'status'        => BusinessStatus::class,
    'opening_hours' => 'array',
];

// Vote
protected $casts = [
    'type' => VoteType::class,
];

// Category
protected $casts = [
    'type' => CategoryType::class,
];
```

---

## Validação nos Form Requests — Obrigatório

```php
use Illuminate\Validation\Rules\Enum;

// StorePostRequest
'status' => ['sometimes', new Enum(PostStatus::class)],

// StoreBusinessRequest
'plan' => ['sometimes', new Enum(BusinessPlan::class)],
```

---

## Models — Relacionamentos e Scopes

```php
// User
public function posts(): HasMany { return $this->hasMany(Post::class); }
public function businesses(): HasMany { return $this->hasMany(Business::class); }
public function comments(): HasMany { return $this->hasMany(Comment::class); }

// Post
public function user(): BelongsTo { return $this->belongsTo(User::class); }
public function category(): BelongsTo { return $this->belongsTo(Category::class); }
public function comments(): HasMany { return $this->hasMany(Comment::class); }
public function votes(): MorphMany { return $this->morphMany(Vote::class, 'votable'); }

public function scopeApproved(Builder $q): Builder {
    return $q->where('status', PostStatus::Approved);
}

// Business
public function user(): BelongsTo { return $this->belongsTo(User::class); }
public function category(): BelongsTo { return $this->belongsTo(Category::class); }
public function photos(): HasMany { return $this->hasMany(BusinessPhoto::class); }
public function promotions(): HasMany { return $this->hasMany(Promotion::class); }
public function coverPhoto(): HasOne {
    return $this->hasOne(BusinessPhoto::class)->where('is_cover', true);
}

public function scopeFeatured(Builder $q): Builder {
    return $q->where('plan', BusinessPlan::Featured)->where('status', BusinessStatus::Approved);
}
public function scopeInNeighborhood(Builder $q, string $neighborhood): Builder {
    return $q->where('neighborhood', 'like', "%{$neighborhood}%");
}

// Promotion
public function business(): BelongsTo { return $this->belongsTo(Business::class); }

public function scopeActive(Builder $q): Builder {
    return $q->where('is_active', true)
             ->where('status', 'approved')
             ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
}
```
