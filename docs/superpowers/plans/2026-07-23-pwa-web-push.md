# FalaVizin PWA and Web Push Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make FalaVizin installable and deliver queued, preference-aware Web Push notifications through the existing Laravel notification system.

**Architecture:** Add the maintained Laravel Web Push channel, keep database notifications as the primary record, and persist one delivery reservation per recipient/event/channel. A root-scoped service worker handles push and a network-only navigation fallback without caching authenticated responses.

**Tech Stack:** Laravel 12.64, Livewire 4.2, PHP 8.5, MySQL 8, Tailwind CSS 4, native Service Worker/Push/Notifications APIs, `laravel-notification-channels/webpush` 11.

---

## File map

**Backend**

- `app/Models/User.php`: push subscription trait and channel preferences.
- `app/Models/NotificationDelivery.php`: persistent delivery ledger.
- `app/Notifications/IdempotentNotification.php`: common channel reservation and completion.
- `app/Notifications/QueuesMailAfterCommit.php`: queue Web Push beside mail.
- Existing scoped Notifications: database and conditional Web Push payloads.
- `app/Notifications/PostVoteNotification.php`: new post reaction notification.
- `app/Actions/UpdatePushSubscriptionAction.php`: ownership transfer, subscription update, and explicit preference activation.
- `app/Actions/DeletePushSubscriptionAction.php`: user-scoped removal.
- `app/Http/Controllers/PushSubscriptionController.php`: thin authenticated endpoints.
- `app/Http/Requests/StorePushSubscriptionRequest.php` and `DeletePushSubscriptionRequest.php`: request boundary validation.
- `app/Providers/AppServiceProvider.php`: release failed delivery reservations.
- `routes/web.php`: authenticated subscription endpoints.

**Frontend**

- `public/manifest.webmanifest`, `public/sw.js`, `public/offline.html`: PWA shell.
- `public/assets/icons/`: generated app, maskable, Apple, and badge icons.
- `resources/js/pwa.js`: installation, subscription, logout cleanup, and settings state.
- `resources/views/components/pwa-install-prompt.blade.php`: non-invasive prompt.
- Layouts and navigation: metadata, prompt, manual installation action.
- Existing notification settings Livewire component and view: per-channel preferences and per-device controls.
- `resources/css/app.css`: standalone safe areas and prompt styling.

**Tests and documentation**

- `tests/Feature/PushSubscriptionTest.php`
- `tests/Feature/WebPushNotificationTest.php`
- `tests/Feature/PwaTest.php`
- Existing comment, vote, queue, and profile tests.
- `docs/PWA_VALIDATION.md`

### Task 1: Install Web Push and add persistence

- [ ] **Step 1: Verify dependency resolution without changing the lock**

Run:

```bash
vendor/bin/sail composer require laravel-notification-channels/webpush:^11.0 --dry-run --no-interaction
```

Expected: Web Push 11.0.0 and Minishlink 10.1.0 resolve with zero updates or removals.

- [ ] **Step 2: Install the resolved dependency**

Run:

```bash
vendor/bin/sail composer require laravel-notification-channels/webpush:^11.0 --no-interaction
vendor/bin/sail php artisan vendor:publish --provider="NotificationChannels\\WebPush\\WebPushServiceProvider" --tag="migrations"
```

Expected: Composer lock contains Web Push 11.0.0 and one unpublished migration creates `push_subscriptions`.

- [ ] **Step 3: Add the delivery ledger migration and model**

Create `notification_deliveries` with:

```php
$table->id();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->string('notification_type');
$table->string('event_key');
$table->string('channel');
$table->timestamp('delivered_at')->nullable();
$table->timestamps();
$table->unique(
    ['user_id', 'notification_type', 'event_key', 'channel'],
    'notification_deliveries_unique',
);
```

`NotificationDelivery` must fill those fields and cast `delivered_at` to
`datetime`.

- [ ] **Step 4: Add migration coverage**

In `tests/Feature/DatabaseConstraintsTest.php`, insert the same
recipient/type/event/channel twice and assert `QueryException`; insert the
same event with a different channel and assert it succeeds.

- [ ] **Step 5: Run the focused test and commit**

```bash
vendor/bin/sail php artisan test --compact tests/Feature/DatabaseConstraintsTest.php
git add composer.json composer.lock app/Models/NotificationDelivery.php database/migrations tests/Feature/DatabaseConstraintsTest.php
git commit -m "feat: add Web Push delivery persistence"
```

### Task 2: Build authenticated subscription management

- [ ] **Step 1: Write failing subscription endpoint tests**

Cover:

```php
$this->postJson(route('push-subscriptions.store'), $payload)->assertUnauthorized();

$this->actingAs($user)
    ->postJson(route('push-subscriptions.store'), $payload)
    ->assertOk();

$this->assertDatabaseHas('push_subscriptions', [
    'subscribable_id' => $user->id,
    'endpoint' => $payload['endpoint'],
]);
```

Also assert endpoint deduplication, reassignment after a different user
explicitly activates it, deletion by owner, and no deletion of another
endpoint.

- [ ] **Step 2: Add validation requests**

`StorePushSubscriptionRequest` rules:

```php
return [
    'endpoint' => ['required', 'url:https', 'max:2048'],
    'keys.p256dh' => ['required', 'string', 'max:512'],
    'keys.auth' => ['required', 'string', 'max:255'],
    'content_encoding' => ['nullable', Rule::in(['aesgcm', 'aes128gcm'])],
    'types' => ['sometimes', 'array', 'min:1'],
    'types.*' => ['string', 'distinct', Rule::in(User::PUSH_NOTIFICATION_TYPES)],
];
```

The delete request validates only the HTTPS endpoint.

- [ ] **Step 3: Implement Actions and controller**

The update Action must globally find the unique endpoint, reassign it to the
current user when explicitly presented by that browser, and save keys through
the package model. It updates `notification_preferences.push` only when
`types` is present.

The delete Action uses the current user's `pushSubscriptions()` relation:

```php
return $user->pushSubscriptions()
    ->where('endpoint', $endpoint)
    ->delete() > 0;
```

The controller returns JSON `200` for update and `204` for an owned deletion;
an unknown endpoint remains idempotent and returns `204`.

- [ ] **Step 4: Register protected routes**

```php
Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])
    ->name('push-subscriptions.store');
Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])
    ->name('push-subscriptions.destroy');
```

- [ ] **Step 5: Run and commit**

```bash
vendor/bin/sail php artisan test --compact tests/Feature/PushSubscriptionTest.php
git add app/Actions app/Http/Controllers/PushSubscriptionController.php app/Http/Requests app/Models/User.php routes/web.php tests/Feature/PushSubscriptionTest.php
git commit -m "feat: manage push subscriptions per device"
```

### Task 3: Add channel preferences and persistent idempotency

- [ ] **Step 1: Write failing preference and delivery tests**

Assert:

```php
$user->notification_preferences = ['moderation' => true];
$this->assertFalse($user->wantsPushNotification('moderation'));

$user->notification_preferences = ['push' => ['moderation' => true]];
$this->assertTrue($user->wantsPushNotification('moderation'));
```

Send the same notification twice and assert one `database` delivery and one
database notification. Send once through another channel and assert its own
ledger row.

- [ ] **Step 2: Implement the idempotency trait**

`shouldSend()` atomically calls:

```php
$delivery = NotificationDelivery::firstOrCreate([
    'user_id' => $notifiable->getKey(),
    'notification_type' => static::class,
    'event_key' => $this->eventKey(),
    'channel' => $this->deliveryChannel($channel),
]);

return $delivery->wasRecentlyCreated;
```

`afterSending()` marks the matching reservation `delivered_at = now()`.
Normalize channels to `database`, `mail`, and `webpush`.

- [ ] **Step 3: Release failed reservations**

In `AppServiceProvider::boot()`, listen for `NotificationFailed`. When the
notification uses the idempotency trait, delete only the matching undelivered
reservation so the queue retry can send again. Log notification class,
notification ID, channel, and user ID without endpoint or keys.

- [ ] **Step 4: Extend queue routing**

Keep `database => sync` and add:

```php
WebPushChannel::class => config('queue.default'),
```

Retain three attempts, `[60, 300]` backoff, and 30-second timeout.

- [ ] **Step 5: Run and commit**

```bash
vendor/bin/sail php artisan test --compact tests/Feature/WebPushNotificationTest.php tests/Feature/QueuedNotificationsTest.php
git add app/Models/User.php app/Notifications app/Providers/AppServiceProvider.php tests/Feature
git commit -m "feat: enforce notification channel preferences and idempotency"
```

### Task 4: Integrate social and account notifications

- [ ] **Step 1: Add failing interaction tests**

Use `Notification::fake()` to assert:

- another user commenting sends `CommentNotification`;
- replying sends it only to the parent author;
- self-comment/self-reply sends nothing;
- a first helpful post vote sends `PostVoteNotification`;
- removing and recreating that vote does not send again.

- [ ] **Step 2: Add Web Push payloads**

Each scoped Notification returns `WebPushMessage` containing:

```php
return (new WebPushMessage)
    ->title($title)
    ->body($body)
    ->icon('/assets/icons/icon-192.png')
    ->badge('/assets/icons/badge-96.png')
    ->tag($this->id)
    ->data(['url' => $relativeUrl]);
```

`via()` always includes `database`, then adds `WebPushChannel::class` only
when `wantsPushNotification($type)` is true and subscriptions exist.

- [ ] **Step 3: Add stable event keys**

Implement the exact keys from the design for comment, comment vote, post vote,
moderation decision, and approved upgrade. Extend moderation notification
callers to pass the moderated entity ID.

- [ ] **Step 4: Gate post reaction dispatch with existing point idempotency**

Change `AwardPointsAction::execute()` to return whether it created the event.
In `VoteButtons`, notify the post author only for `helpful`, another user, and
a newly created point event.

- [ ] **Step 5: Run and commit**

```bash
vendor/bin/sail php artisan test --compact tests/Feature/CommentTest.php tests/Feature/VoteTest.php tests/Feature/ModerationTest.php tests/Feature/WebPushNotificationTest.php
git add app/Actions/AwardPointsAction.php app/Livewire/Feed app/Notifications app/Http/Controllers tests/Feature
git commit -m "feat: send queued push for user interactions"
```

### Task 5: Add manifest, icons, service worker, and metadata

- [ ] **Step 1: Generate icons from the existing mark**

Use the existing Intervention Image/GD runtime to generate exact 192, 512,
180, and 96 pixel files. Maskable variants use `#FD5C3E` with the mark inside
the central safe zone. Verify dimensions with `identify` or PHP image metadata.

- [ ] **Step 2: Add manifest and offline page**

Manifest values:

```json
{
  "name": "FalaVizin",
  "short_name": "FalaVizin",
  "start_url": "/",
  "scope": "/",
  "display": "standalone",
  "theme_color": "#FD5C3E",
  "background_color": "#FAFAF9"
}
```

Include `any` and `maskable` 192/512 icons. `offline.html` contains no forms,
session data, external fonts, or dynamic scripts.

- [ ] **Step 3: Add the safe service worker**

The fetch listener exits unless:

```js
event.request.method === 'GET'
    && event.request.mode === 'navigate'
    && new URL(event.request.url).origin === self.location.origin
```

It performs `fetch(event.request)` and returns cached `/offline.html` only on
network rejection. It never caches the response.

The push handler parses minimal JSON. The click handler validates same-origin
URLs, focuses and navigates an existing client, or opens a new one.

- [ ] **Step 4: Add layout metadata**

Both app and guest layouts receive manifest, theme color, Apple capable/status
metadata, and Apple Touch icon. The app layout exposes CSRF, authenticated user
ID, VAPID public key, and whether the current route permits the automatic
install suggestion.

- [ ] **Step 5: Add PWA HTTP tests and commit**

Assert manifest fields, public file responses, and service-worker source
guards for GET/navigation.

```bash
vendor/bin/sail php artisan test --compact tests/Feature/PwaTest.php
git add public resources/views/layouts tests/Feature/PwaTest.php
git commit -m "feat: add installable PWA shell"
```

### Task 6: Build installation and per-device UI

- [ ] **Step 1: Implement `resources/js/pwa.js`**

Register `/sw.js`; retain `beforeinstallprompt`; do not call `prompt()` until
the install button is clicked. Store dismissal as an epoch timestamp and
suppress automatic prompting for 14 days.

iOS detection combines `navigator.userAgentData?.platform`,
`navigator.platform`, `navigator.maxTouchPoints`, and a user-agent fallback.
Instructions appear only on iOS/iPadOS when not standalone and no browser
install prompt exists.

- [ ] **Step 2: Implement explicit push activation**

The settings UI begins with all unchecked event types unless already enabled.
Disable “Ativar neste dispositivo” until at least one type is selected. On
click: request permission, subscribe using the VAPID public key, then POST the
subscription and selected types with CSRF.

Denied permission renders guidance and never invokes the native prompt again.
Unsupported browsers render a static explanation.

- [ ] **Step 3: Implement disable and logout cleanup**

Disable sends the endpoint to DELETE, calls `unsubscribe()`, and reports that
other devices remain active.

Intercept logout form submission, perform the same cleanup with a short
timeout, then call the native `form.submit()` so cleanup cannot recursively
intercept or block logout.

- [ ] **Step 4: Add install surfaces**

Add the bottom prompt to the app layout and “Instalar aplicativo” to desktop
and mobile user menus. Automatic display is allowed on home, feed/show,
business/show, promotions, events, ranking, pulso, and search routes whether
the visitor is authenticated or not. Exclude auth, create, edit, profile, and
admin routes.

- [ ] **Step 5: Update notification settings**

`NotificationSettings::togglePreference($channel, $type)` validates both
against constants, preserves all unrelated JSON keys, and defaults missing
push keys to false. The view shows database as always active, e-mail only for
existing mail types, and push switches for scoped types.

- [ ] **Step 6: Build and commit**

```bash
vendor/bin/sail npm run build
vendor/bin/sail php artisan test --compact tests/Feature/ProfileTest.php tests/Feature/PushSubscriptionTest.php tests/Feature/PwaTest.php
git add resources app/Livewire/Profile resources/views routes tests
git commit -m "feat: add PWA install and device push controls"
```

### Task 7: Final verification and deployment documentation

- [ ] **Step 1: Add environment template**

```env
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
VAPID_SUBJECT=https://falavizin.com.br
```

- [ ] **Step 2: Write manual validation**

`docs/PWA_VALIDATION.md` must contain exact checks for Android Chromium,
desktop Chromium/Safari, macOS Safari, and installed iOS/iPadOS 16.4+, plus
permission denied, offline fallback, logout, and multi-account behavior.

- [ ] **Step 3: Format and run all checks**

```bash
vendor/bin/sail php artisan migrate
vendor/bin/sail vendor/bin/pint --test
vendor/bin/sail php artisan test --compact
vendor/bin/sail npm run build
vendor/bin/sail composer audit --locked
vendor/bin/sail npm audit --audit-level=high
```

Expected: migrations complete, zero formatting errors, all tests pass, Vite
build succeeds, and no high-severity audit finding blocks deployment.

- [ ] **Step 4: Commit final documentation and environment changes**

```bash
git add .env.example docs/PWA_VALIDATION.md
git commit -m "docs: add PWA deployment and validation guide"
git status --short
```

Expected: clean worktree.
