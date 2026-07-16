<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api/auth')->group(function () {
    Route::get('planning-center/redirect', [AuthController::class, 'redirect']);
    Route::get('planning-center/callback', [AuthController::class, 'callback']);
});

Route::get('/test-mail-preview', function () {
    return (new App\Mail\GenericMail(
        'John',
        'Thank you for reaching out to us.<br><br>We will get back to you shortly.',
        'Visit Our Website',
        'https://aimchurch.com'
    ))->render();
});

use App\Mail\NewInquiryNotification;

Route::get('/test-staff-mail-preview', function () {
    return (new NewInquiryNotification(
        'Jane Smith',
        'jane.smith@example.com',
        '010-1234-5678',
        'Hi, I would like to know more about your Sunday service times and whether you have a children\'s ministry program.',
        now()->format('F j, Y g:i A')
    ))->render();
});

Route::get('/test-mail-send', function (\Illuminate\Http\Request $request) {
    if ($request->query('secret') !== config('services.internal.task_secret')) {
        abort(403);
    }

    $to = $request->query('to');
    if (!$to) {
        return 'Usage: ?to=your@email.com&secret=...';
    }

    \Illuminate\Support\Facades\Mail::to($to)->send(new App\Mail\GenericMail(
        'Test',
        'This is a deployment SMTP test email — Render → Mailtrap.',
        'Visit Our Website',
        'https://aimchurch.com'
    ));

    return "Sent to {$to} via mailer: " . config('mail.default');
});

// Diagnosing Speed
Route::get('/__diag', function () {
    $t0 = microtime(true);

    \DB::select('select 1');
    $t1 = microtime(true);

    \Illuminate\Support\Facades\Cache::put('diag_test', 'ok', 10);
    \Illuminate\Support\Facades\Cache::get('diag_test');
    $t2 = microtime(true);

    return response()->json([
        'db_ms'    => round(($t1 - $t0) * 1000, 1),
        'redis_ms' => round(($t2 - $t1) * 1000, 1),
        'total_ms' => round(($t2 - $t0) * 1000, 1),
    ]);
});

Route::get('/__diag2', function () {
    $boot = microtime(true) - LARAVEL_START;

    $t0 = microtime(true);
    $html = view('welcome')->render(); // 프로젝트에 있는 아무 blade 뷰로 교체 가능
    $renderMs = round((microtime(true) - $t0) * 1000, 1);

    return response()->json([
        'boot_ms'   => round($boot * 1000, 1),   // 요청 시작~이 라우트 도달까지
        'render_ms' => $renderMs,                 // 뷰 렌더링만
        'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status() !== false,
    ]);
});
