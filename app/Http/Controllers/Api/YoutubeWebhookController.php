<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SermonService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class YoutubeWebhookController extends Controller
{
    public function __construct(
        private readonly SermonService $sermonService,
    ) {}

    public function handle(Request $request): Response
    {
        if ($request->isMethod('get')) {
            return $this->verifySubscription($request);
        }

        return $this->receiveNotification($request);
    }

    private function verifySubscription(Request $request): Response
    {
        $challenge = $request->query('hub_challenge');

        if (!$challenge) {
            Log::warning('YouTube 구독 검증 요청에 hub_challenge 없음', [
                'query' => $request->query(),
            ]);

            return response('Missing hub_challenge', 400);
        }

        Log::info('YouTube 구독 검증 성공', [
            'mode'  => $request->query('hub_mode'),
            'topic' => $request->query('hub_topic'),
        ]);

        return response($challenge, 200);
    }

    private function receiveNotification(Request $request): Response
    {
        if (!$this->hasValidSignature($request)) {
            Log::warning('YouTube webhook: invalid signature, ignoring notification.');
            return response('', 204);
        }

        Log::info('YouTube 웹훅 알림 수신 — sermons 캐시 무효화 실행');

        // We don't need to parse the Atom XML body in detail — any notification
        // means the uploads feed changed, so we just invalidate the cache and
        // let the next read re-fetch via playlistItems.list (1 quota unit).
        $this->sermonService->invalidateAllSermonsCache();

        return response('', 204);
    }

    private function hasValidSignature(Request $request): bool
    {
        $secret = config('services.youtube.webhook_secret');

        if (!$secret) {
            return true;
        }

        $header = $request->header('X-Hub-Signature', '');

        if (!str_starts_with($header, 'sha1=')) {
            return false;
        }

        $expected = 'sha1=' . hash_hmac('sha1', $request->getContent(), $secret);

        return hash_equals($expected, $header);
    }
}
