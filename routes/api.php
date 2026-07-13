<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventLikeController;
use App\Http\Controllers\Api\EventRegistrationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SermonController;
use App\Http\Controllers\Api\YoutubeWebhookController;

// ─── Auth ─────────────────────────────────────────────────────────────────────

Route::prefix('auth')->group(function () {
    Route::middleware('auth:planning_center')->group(function () {   // auth:sanctum → auth:planning_center
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

// ─── Events (Public) ──────────────────────────────────────────────────────────

Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/{event}', [EventController::class, 'show']);
});

// ─── Events (Authenticated) ───────────────────────────────────────────────────

Route::middleware('auth:planning_center')->prefix('events')->group(function () {
    Route::post('/{event}/like', [EventLikeController::class, 'toggle']);
    Route::post('/{event}/register', [EventRegistrationController::class, 'store']);
    Route::delete('/{event}/register', [EventRegistrationController::class, 'cancel']);
});

// ─── Announcements ────────────────────────────────────────────────────────────

Route::prefix('announcements')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index']);
    Route::get('/{announcement}', [AnnouncementController::class, 'show']);
});

// ─── Sermons (Public) ──────────────────────────────────────────────────────

Route::prefix('sermons')->group(function () {
    Route::get('/', [SermonController::class, 'index']);
    Route::get('/live', [SermonController::class, 'live']);        // must come before /{id}
    Route::get('/upcoming', [SermonController::class, 'upcoming']); // must come before /{id}
    Route::get('/{id}', [SermonController::class, 'show']);
});

// ─── YouTube PubSubHubbub Webhook ──────────────────────────────────────────
// No session/CSRF needed — server-to-server callback from Google's hub.

Route::match(['get', 'post'], 'youtube/webhook', [YoutubeWebhookController::class, 'handle']);
