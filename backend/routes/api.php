<?php

use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\TournamentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check (no middleware)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Simple test
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'config_loaded' => config('tournament.total_tables'),
    ]);
});

Route::middleware(['throttle:api'])->group(function () {
    // Player Authentication routes (PUBLIC)
    Route::post('/player/register', [PlayerController::class, 'register']);
    Route::post('/player/login', [PlayerController::class, 'login']);
    
    // Statistics routes (PUBLIC)
    Route::get('/statistics', [StatisticsController::class, 'index']);
    Route::get('/statistics/tournament/{id}', [StatisticsController::class, 'tournament']);
    
    // Tournament routes (PUBLIC - with optional authentication detection)
    Route::get('/tournaments', [TournamentController::class, 'index']);
    Route::get('/tournaments/featured', [TournamentController::class, 'featured']);
    Route::get('/tournaments/upcoming', [TournamentController::class, 'upcoming']);
    Route::get('/tournaments/open', [TournamentController::class, 'open']);
    Route::get('/tournaments/types', [TournamentController::class, 'types']);
    Route::get('/tournaments/{id}', [TournamentController::class, 'show']);
    Route::get('/tournaments/{id}/statistics', [TournamentController::class, 'statistics']);
    Route::get('/tournaments/{id}/tables', [TournamentController::class, 'tables']);
    Route::get('/tournaments/{id}/registered-players', [TournamentController::class, 'registeredPlayers']);
    Route::get('/tournaments/{id}/waiting-list', [TournamentController::class, 'tournamentWaitingList']);
    Route::get('/tournament/{slug}', [TournamentController::class, 'showBySlug']);
    
    // QR Check-in route (PUBLIC - for scanning QR codes)
    Route::post('/checkin', [RegistrationController::class, 'checkIn']);
    
    // Public statistics and viewing routes
    Route::get('/registrations', [RegistrationController::class, 'index']);
    Route::get('/registration/{id}', [RegistrationController::class, 'show']);
    Route::get('/registration/phone/{phone}', [RegistrationController::class, 'getByPhone']);
    Route::get('/registration/statistics', [RegistrationController::class, 'statistics']);
    Route::get('/registration/tables', [RegistrationController::class, 'tableLayout']);
    Route::get('/registration/waiting-list', [RegistrationController::class, 'waitingList']);
    
    // Protected routes (REQUIRE AUTHENTICATION)
    Route::middleware('auth:sanctum')->group(function () {
        // Player profile routes
        Route::get('/player/profile', [PlayerController::class, 'profile']);
        Route::put('/player/profile', [PlayerController::class, 'updateProfile']);
        Route::post('/player/change-password', [PlayerController::class, 'changePassword']);
        Route::post('/player/logout', [PlayerController::class, 'logout']);
        Route::get('/player/tournament-history', [PlayerController::class, 'tournamentHistory']);
        
        // Tournament registration (REQUIRES LOGIN)
        Route::post('/register', [RegistrationController::class, 'register']);
        Route::post('/registration/{id}/cancel', [RegistrationController::class, 'cancel']);
    });
    
    // Legacy routes (DEPRECATED - kept for backwards compatibility)
    Route::post('/reserve', [ReservationController::class, 'store'])->name('api.reserve.legacy');
    Route::get('/reservation/{id}', [ReservationController::class, 'show'])->name('api.reservation.show.legacy');
    Route::get('/reservation/phone/{phone}', [ReservationController::class, 'getByPhone'])->name('api.reservation.phone.legacy');
    Route::post('/reservation/{id}/cancel', [ReservationController::class, 'cancel'])->name('api.reservation.cancel.legacy');
    Route::get('/tables', [ReservationController::class, 'tableLayout'])->name('api.tables.legacy');
    Route::get('/waiting-list', [ReservationController::class, 'waitingList'])->name('api.waiting-list.legacy');
});

