<?php

use App\Http\Controllers\Admin\ClaimController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\SponsoredPostsController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClaimBusinessController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegacyNeighborhoodRedirectController;
use App\Http\Controllers\NeighborhoodSelectionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PulsoController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UserProfileController;
use App\Livewire\Admin\GooglePlacesImport;
use App\Livewire\Admin\NeighborhoodManager;
use App\Models\Neighborhood;
use Illuminate\Support\Facades\Route;

// Públicas
Route::get('/health', [HealthController::class, 'check'])->name('health.check');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Neighborhood-scoped routes
Route::prefix('{state}/{city}/{neighborhood}')
    ->where([
        'state' => '[a-z]{2}',
        'city' => '[a-z0-9-]+',
        'neighborhood' => '[a-z0-9-]+',
    ])
    ->middleware('neighborhood')
    ->name('neighborhood.')
    ->group(function (): void {
        Route::middleware('neighborhood.active')->group(function (): void {
            Route::get('/', [HomeController::class, 'local'])->name('home');
            Route::get('/busca', [SearchController::class, 'index'])->name('search.index');
            Route::get('/feed', [PostController::class, 'index'])->name('feed.index');
            Route::get('/servicos', [BusinessController::class, 'index'])->name('businesses.index');
            Route::get('/servicos/mapa', [BusinessController::class, 'map'])->name('businesses.map');
            Route::get('/categoria/{category}', [CategoryController::class, 'show'])->withoutScopedBindings()->name('categories.show');
            Route::get('/promocoes', [PromotionController::class, 'index'])->name('promotions.index');
            Route::get('/pulso', [PulsoController::class, 'index'])->name('pulso.index');
            Route::get('/eventos', fn () => view('events.index', ['neighborhood' => request()->route('neighborhood')]))
                ->name('events.index');
        });

        Route::middleware(['auth', 'neighborhood.active'])->group(function (): void {
            Route::get('/criar-post', [PostController::class, 'create'])->name('feed.create');
            Route::get('/cadastrar-negocio', [BusinessController::class, 'create'])->name('businesses.create');
            Route::post('/cadastrar-negocio', [BusinessController::class, 'store'])->name('businesses.store');
            Route::get('/meu-negocio/{business:slug}/editar', [BusinessController::class, 'edit'])->scopeBindings()->name('businesses.edit');
            Route::put('/meu-negocio/{business:slug}', [BusinessController::class, 'update'])->scopeBindings()->name('businesses.update');
            Route::get('/meu-negocio/{business:slug}/configuracao', [BusinessController::class, 'onboarding'])->scopeBindings()->name('businesses.onboarding');
            Route::get('/meu-negocio/{business:slug}/qr', [BusinessController::class, 'qr'])->scopeBindings()->name('businesses.qr');
            Route::post('/meu-negocio/{business:slug}/qr/confirmar', [BusinessController::class, 'confirmQr'])->scopeBindings()->name('businesses.qr.confirm');
            Route::get('/meu-negocio/{business:slug}/qr/download', [BusinessController::class, 'downloadQr'])->scopeBindings()->name('businesses.qr.download');
            Route::get('/meu-negocio/{business:slug}/qr/download-pdf', [BusinessController::class, 'downloadQrPdf'])->scopeBindings()->name('businesses.qr.download-pdf');

            Route::post('/meu-negocio/{business:slug}/promocoes', [PromotionController::class, 'store'])
                ->scopeBindings()
                ->name('promotions.store');

            Route::post('/meu-negocio/{business:slug}/solicitar-upgrade', [BusinessController::class, 'requestUpgrade'])
                ->scopeBindings()
                ->name('businesses.upgrade.request');
        });

        Route::middleware('auth')->group(function (): void {
            Route::get('/feed/{post:slug}/editar', [PostController::class, 'edit'])->scopeBindings()->name('feed.edit');
            Route::delete('/feed/{post:slug}', [PostController::class, 'destroy'])->scopeBindings()->name('feed.destroy');

            Route::delete('/servicos/{business:slug}', [BusinessController::class, 'destroy'])->scopeBindings()->name('businesses.destroy');

            Route::post('/servicos/{business}/reivindicar', [ClaimBusinessController::class, 'request'])
                ->middleware('throttle:5,60')
                ->name('businesses.claim.request');
        });

        Route::get('/feed/{post:slug}', [PostController::class, 'show'])
            ->scopeBindings()
            ->name('feed.show');
        Route::get('/servicos/{business:slug}', [BusinessController::class, 'show'])
            ->scopeBindings()
            ->name('businesses.show');
    });

// Legacy redirects (temporary — will be removed as tasks migrate consumers)
// These keep the old route names for backward compatibility
Route::get('/feed', [LegacyNeighborhoodRedirectController::class, 'index'])->defaults('type', 'feed')->name('feed.index');
Route::get('/servicos', [LegacyNeighborhoodRedirectController::class, 'index'])->defaults('type', 'servicos')->name('businesses.index');
Route::get('/pulso', [LegacyNeighborhoodRedirectController::class, 'index'])->defaults('type', 'pulso')->name('pulso.index');
Route::get('/servicos/mapa', [BusinessController::class, 'map'])->name('businesses.map');
Route::get('/feed/{post:slug}', [LegacyNeighborhoodRedirectController::class, 'post'])->name('feed.show');
Route::get('/servicos/{business:slug}', [LegacyNeighborhoodRedirectController::class, 'business'])->name('businesses.show');

// Original routes (temporary — kept for compatibility until tasks migrate consumers)
Route::get('/', [HomeController::class, 'directory'])->name('home');
Route::get('/busca', [SearchController::class, 'index'])->name('search.index');
Route::get('/u/{user}', [UserProfileController::class, 'show'])->name('users.show');
Route::get('/categoria/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/promocoes', [PromotionController::class, 'index'])->name('promotions.index');
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');
Route::get('/eventos', fn () => view('events.index'))->name('events.index');

// Neighborhood selection (authenticated, no primary-neighborhood requirement)
Route::middleware('auth')->group(function () {
    Route::get('/escolher-bairro', [NeighborhoodSelectionController::class, 'create'])
        ->name('neighborhoods.select');
    Route::patch('/meu-bairro', [NeighborhoodSelectionController::class, 'update'])
        ->name('neighborhoods.update');
});

// Autenticadas
Route::middleware(['auth', 'primary-neighborhood'])->group(function () {
    Route::get('/minha-conta', [ProfileController::class, 'account'])->name('profile.account');
    Route::get('/notificacoes', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])
        ->name('push-subscriptions.store');
    Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])
        ->name('push-subscriptions.destroy');

    Route::get('/criar-post', [PostController::class, 'create'])->name('feed.create');
    Route::delete('/feed/{post}', [PostController::class, 'destroy'])->name('feed.destroy');

    Route::get('/cadastrar-negocio', [BusinessController::class, 'create'])->name('businesses.create');
    Route::post('/cadastrar-negocio', [BusinessController::class, 'store'])->name('businesses.store');
    Route::get('/meu-negocio/{business}/editar', [BusinessController::class, 'edit'])->name('businesses.edit');
    Route::put('/meu-negocio/{business}', [BusinessController::class, 'update'])->name('businesses.update');
    Route::get('/meu-negocio/{business}/configuracao', [BusinessController::class, 'onboarding'])->name('businesses.onboarding');
    Route::get('/meu-negocio/{business}/qr', [BusinessController::class, 'qr'])->name('businesses.qr');
    Route::post('/meu-negocio/{business}/qr/confirmar', [BusinessController::class, 'confirmQr'])->name('businesses.qr.confirm');
    Route::get('/meu-negocio/{business}/qr/download', [BusinessController::class, 'downloadQr'])->name('businesses.qr.download');
    Route::get('/meu-negocio/{business}/qr/download-pdf', [BusinessController::class, 'downloadQrPdf'])->name('businesses.qr.download-pdf');
    Route::post('/meu-negocio/{business}/solicitar-upgrade', [BusinessController::class, 'requestUpgrade'])->name('businesses.upgrade.request');

    Route::delete('/promocoes/{promotion}', [PromotionController::class, 'destroy'])
        ->name('promotions.destroy');

    Route::post('/feed/{post}/reportar', [ReportController::class, 'post'])->name('report.post')->middleware('throttle:10,1');
    Route::post('/servicos/{business}/reportar', [ReportController::class, 'business'])->name('report.business')->middleware('throttle:10,1');
    Route::post('/promocoes/{promotion}/reportar', [ReportController::class, 'promotion'])->name('report.promotion')->middleware('throttle:10,1');
});

// Analytics tracking (authenticated but no CSRF for AJAX)
Route::middleware('auth')->group(function () {
    Route::post('/negocio/{business}/rastrear/{eventType}', function (Business $business, string $eventType) {
        if (! in_array($eventType, ['phone_click', 'whatsapp_click'])) {
            abort(422);
        }
        BusinessAnalytics::record($business, $eventType);

        return response()->json(['ok' => true]);
    })->name('business.track');

    Route::post('/negocio/{business}/compartilhar', [BusinessController::class, 'trackShare'])->name('businesses.share.track');
});

// Moderation (admin + moderator)
Route::middleware(['auth', 'moderator'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/moderacao', [ModerationController::class, 'index'])->name('moderation.index');
    Route::post('/moderacao/em-massa', [ModerationController::class, 'bulk'])->name('moderation.bulk');
    Route::post('/moderacao/{type}/{id}/aprovar', [ModerationController::class, 'approve'])->name('moderation.approve');
    Route::post('/moderacao/{type}/{id}/rejeitar', [ModerationController::class, 'reject'])->name('moderation.reject');
});

// Admin only
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/usuarios', [UserManagementController::class, 'index'])->name('users.index');
    Route::patch('/usuarios/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.update-role');

    Route::get('/reivindicacoes', [ClaimController::class, 'index'])->name('claims.index');
    Route::post('/reivindicacoes/{claim}/aprovar', [ClaimController::class, 'approve'])->name('claims.approve');
    Route::post('/reivindicacoes/{claim}/rejeitar', [ClaimController::class, 'reject'])->name('claims.reject');
    Route::get('/importar-google-places', GooglePlacesImport::class)->name('google-places-import');
    Route::get('/bairros', NeighborhoodManager::class)->name('neighborhoods');
    Route::get('/estatisticas', [StatsController::class, 'index'])->name('stats');
    Route::post('/negocio/{business}/aprovar-upgrade', [BusinessController::class, 'approveUpgrade'])->name('businesses.upgrade.approve');
    Route::post('/negocio/{business}/dispensar-upgrade', [BusinessController::class, 'dismissUpgrade'])->name('businesses.upgrade.dismiss');

    Route::get('/posts-patrocinados', [SponsoredPostsController::class, 'index'])->name('sponsored-posts.index');
    Route::post('/posts-patrocinados/{post}/toggle', [SponsoredPostsController::class, 'toggle'])->name('sponsored-posts.toggle');
});

require __DIR__.'/auth.php';
