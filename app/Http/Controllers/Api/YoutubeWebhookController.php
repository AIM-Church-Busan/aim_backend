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

    /**
     * Handles both:
     * - GET: PubSubHubbub subscription verification (must echo hub_challenge back)
     * - POST: new/updated video notification (Atom XML body)
     */
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
            return response('Missing hub_challenge', 400);
        }

        return response($challenge, 200);
    }

    private function receiveNotification(Request $request): Response
    {
        if (!$this->hasValidSignature($request)) {
            Log::warning('YouTube webhook: invalid signature, ignoring notification.');
            return response('', 204);
        }

        // We don't need to parse the Atom XML body in detail — any notification
        // means the uploads feed changed, so we just invalidate the cache and
        // let the next read re-fetch via playlistItems.list (1 quota unit).
        $this->sermonService->invalidateAllSermonsCache();

        return response('', 204);
    }

    private function hasValidSignature(Request $request): bool
    {
        $secret = config('services.youtube.webhook_secret');

        // If no secret is configured, skip verification (not recommended for production).
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
