<?php

use App\Http\Controllers\Admin\AuctionController as AdminAuctionController;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('auctions.index'));

// Breeze redirects here after login — send users straight to auctions
Route::get('/dashboard', fn () => redirect()->route('auctions.index'))
    ->middleware('auth')
    ->name('dashboard');

// ── Public auction pages ──────────────────────────────────────────────────────
Route::get('/auctions',           [AuctionController::class, 'index'])->name('auctions.index');
Route::get('/auctions/{auction}', [AuctionController::class, 'show'])->name('auctions.show');

// ── Authenticated actions ─────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/auctions/{auction}/bids', [BidController::class, 'store'])
        ->name('bids.store');

    Route::get('/checkout/{auction}',  [CheckoutController::class, 'show'])
        ->name('checkout.show');
    Route::post('/checkout/{auction}', [CheckoutController::class, 'store'])
        ->name('checkout.store');

    // Breeze profile routes
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Admin ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', AdminMiddleware::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('auctions', AdminAuctionController::class)
            ->except(['show']);
    });

require __DIR__.'/auth.php';
