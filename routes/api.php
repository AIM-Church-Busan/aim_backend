<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventLikeController;
use App\Http\Controllers\Api\EventRegistrationController;
use Illuminate\Support\Facades\Route;

// ─── Auth ─────────────────────────────────────────────────────────────────────

Route::prefix('auth')->group(function () {
    Route::get('planning-center/redirect', [AuthController::class, 'redirect']);
    Route::get('planning-center/callback', [AuthController::class, 'callback']);

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

Route::middleware('auth:sanctum')->prefix('events')->group(function () {
    Route::post('/{event}/like', [EventLikeController::class, 'toggle']);
    Route::post('/{event}/register', [EventRegistrationController::class, 'store']);
    Route::delete('/{event}/register', [EventRegistrationController::class, 'cancel']);
});

// ─── Announcements ────────────────────────────────────────────────────────────

Route::prefix('announcements')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index']);
    Route::get('/{announcement}', [AnnouncementController::class, 'show']);
});
