<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SwimController;
use App\Http\Middleware\SwimPinAuth;

Route::get('/', fn() => view('timer'));
Route::get('/timer', fn() => view('timer'));

// Swim API under web middleware (needs cookies for PIN auth)
// CSRF disabled for these JSON API endpoints
Route::prefix('api/swim')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->group(function () {
    // Open: timer can always save sessions + get next team number
    Route::post('/sessions', [SwimController::class, 'store']);
    Route::get('/teams/next-name', [SwimController::class, 'nextTeamName']);

    // PIN verification (sets cookie)
    Route::post('/auth', function () {})->middleware(SwimPinAuth::class);

    // Protected: viewing data & exports
    Route::middleware(SwimPinAuth::class)->group(function () {
        Route::get('/sessions', [SwimController::class, 'index']);
        Route::get('/sessions/{session}', [SwimController::class, 'show']);
        Route::delete('/sessions/{session}', [SwimController::class, 'destroy']);
        Route::get('/sessions/{session}/export', [SwimController::class, 'exportSession']);

        Route::get('/swimmers/stats', [SwimController::class, 'swimmerStats']);
        Route::get('/swimmers/export', [SwimController::class, 'exportSwimmers']);
        Route::get('/swimmers/{name}/log', [SwimController::class, 'swimmerLog']);

    });
});
