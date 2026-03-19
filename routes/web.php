<?php

use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClaimBusinessController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Livewire\Admin\AppSettings;
use App\Livewire\Admin\GooglePlacesImport;
use Illuminate\Support\Facades\Route;

// Públicas
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/busca', [SearchController::class, 'index'])->name('search.index');
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/feed', [PostController::class, 'index'])->name('feed.index');
Route::get('/feed/{post:slug}', [PostController::class, 'show'])->name('feed.show');
Route::get('/servicos', [BusinessController::class, 'index'])->name('businesses.index');
Route::get('/servicos/{business:slug}', [BusinessController::class, 'show'])->name('businesses.show');
Route::get('/categoria/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/promocoes', [PromotionController::class, 'index'])->name('promotions.index');

// Autenticadas
Route::middleware('auth')->group(function () {
    Route::get('/minha-conta', [ProfileController::class, 'account'])->name('profile.account');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/criar-post', [PostController::class, 'create'])->name('feed.create');
    Route::post('/criar-post', [PostController::class, 'store'])->name('feed.store');
    Route::delete('/feed/{post}', [PostController::class, 'destroy'])->name('feed.destroy');

    Route::get('/cadastrar-negocio', [BusinessController::class, 'create'])->name('businesses.create');
    Route::post('/cadastrar-negocio', [BusinessController::class, 'store'])->name('businesses.store');
    Route::get('/meu-negocio/{business}/editar', [BusinessController::class, 'edit'])->name('businesses.edit');
    Route::put('/meu-negocio/{business}', [BusinessController::class, 'update'])->name('businesses.update');

    Route::post('/servicos/{business}/reivindicar', [ClaimBusinessController::class, 'request'])
        ->name('businesses.claim.request');
    Route::get('/reivindicar/{token}', [ClaimBusinessController::class, 'verify'])
        ->name('businesses.claim.verify');

    Route::post('/meu-negocio/{business}/promocoes', [PromotionController::class, 'store'])
        ->name('promotions.store');
    Route::delete('/promocoes/{promotion}', [PromotionController::class, 'destroy'])
        ->name('promotions.destroy');

    Route::post('/feed/{post}/reportar', [ReportController::class, 'post'])->name('report.post');
    Route::post('/servicos/{business}/reportar', [ReportController::class, 'business'])->name('report.business');
    Route::post('/promocoes/{promotion}/reportar', [ReportController::class, 'promotion'])->name('report.promotion');
});

// Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/moderacao', [ModerationController::class, 'index'])->name('moderation.index');
    Route::post('/moderacao/{type}/{id}/aprovar', [ModerationController::class, 'approve'])->name('moderation.approve');
    Route::post('/moderacao/{type}/{id}/rejeitar', [ModerationController::class, 'reject'])->name('moderation.reject');
    Route::get('/importar-google-places', GooglePlacesImport::class)->name('google-places-import');
    Route::get('/configuracoes', AppSettings::class)->name('settings');
});

require __DIR__.'/auth.php';
