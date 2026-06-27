<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventLikeController;
use App\Http\Controllers\Api\EventRegistrationController;
use Illuminate\Support\Facades\Route;

// ─── Public Routes ────────────────────────────────────────────────────────────

Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/{event}', [EventController::class, 'show']);
});

// ─── Authenticated Routes ─────────────────────────────────────────────────────

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
