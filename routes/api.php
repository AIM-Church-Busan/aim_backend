<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventLikeController;
use App\Http\Controllers\Api\EventRegistrationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SermonController;
use App\Http\Controllers\Api\YoutubeWebhookController;
use Illuminate\Support\Facades\Artisan;

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

Route::match(['get', 'post'], 'youtube/webhook', [YoutubeWebhookController::class, 'handle'])
    ->name('youtube.webhook');

// ─── Internal Task Trigger (셸 접근 불가한 배포 환경용) ──────────────────────────

Route::post('internal/youtube/subscribe', function (Request $request) {
    if ($request->header('X-Internal-Secret') !== config('services.internal.task_secret')) {
        abort(403);
    }

    Artisan::call('youtube:subscribe');

    return response()->json([
        'output' => Artisan::output(),
    ]);
});
