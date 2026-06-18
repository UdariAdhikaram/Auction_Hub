<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuctionController;
use App\Http\Controllers\Api\BidController;
use App\Http\Controllers\Api\WatchlistController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Auction routes
    Route::apiResource('auctions', AuctionController::class);

    // Bid routes with KYC middleware
    Route::middleware('kyc')->group(function () {
        Route::post('/auctions/{auction}/bids', [BidController::class, 'store'])
            ->middleware('throttle:bids');
    });

    // Watchlist routes
    Route::get('/me/watchlist', [WatchlistController::class, 'index']);
    Route::post('/watchlist', [WatchlistController::class, 'store']);
    Route::delete('/watchlist/{auction}', [WatchlistController::class, 'destroy']);
});
