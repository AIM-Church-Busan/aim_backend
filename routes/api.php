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
use Illuminate\Http\Request;
use App\Http\Controllers\Api\InstagramAuthController;

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

Route::post('internal/artisan/{command}', function (Request $request, string $command) {
    if ($request->header('X-Internal-Secret') !== config('services.internal.task_secret')) {
        abort(403);
    }

    $allowedCommands = ['youtube:subscribe', 'instagram:refresh-token'];

    if (!in_array($command, $allowedCommands)) {
        abort(404);
    }

    Artisan::call($command);

    return response()->json(['output' => Artisan::output()]);
});

Route::get('debug/log-test', function () {
    \Log::info('로그 테스트 — 이게 보이면 stdout 로그 정상 작동');
    return 'ok';
});

Route::get('debug/raw-log-test', function () {
    error_log('RAW STDERR TEST — PHP 순수 함수로 직접 씀');
    \Log::info('LARAVEL LOG TEST — Log 파사드로 씀');
    return 'ok';
});

// ─── Instagram Auth ───────────────────────────────────────────────────────────

Route::prefix('instagram/auth')->group(function () {
    Route::get('redirect', [InstagramAuthController::class, 'redirect']);
    Route::get('callback', [InstagramAuthController::class, 'callback']);
});

