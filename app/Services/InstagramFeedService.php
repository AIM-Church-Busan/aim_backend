<?php

namespace App\Services;

use App\Models\InstagramToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramFeedService
{
    private const CACHE_KEY = 'instagram:feed';
    private const FALLBACK_CACHE_KEY = 'instagram:feed:fallback';

    public function getFeed(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            (int) config('services.instagram.cache_ttl', 1800),
            fn () => $this->fetchFromApi()
        );
    }

    /**
     * instagram:refresh-feed 커맨드에서 강제로 캐시를 최신화할 때 사용.
     */
    public function refreshFeed(): array
    {
        $feed = $this->fetchFromApi();

        Cache::put(self::CACHE_KEY, $feed, (int) config('services.instagram.cache_ttl', 1800));

        return $feed;
    }

    private function fetchFromApi(): array
    {
        $token = InstagramToken::query()->latest('expires_at')->first();

        if (!$token) {
            Log::warning('Instagram 토큰이 DB에 없습니다.');
            return $this->fallback();
        }

        $response = Http::get('https://graph.instagram.com/me/media', [
            'fields'       => 'id,caption,media_type,media_url,permalink,timestamp',
            'access_token' => $token->access_token,
        ]);

        if (!$response->successful()) {
            Log::error('Instagram 피드 fetch 실패', ['body' => $response->body()]);
            return $this->fallback();
        }

        $data = $response->json('data', []);

        // 정상 조회 성공 시 fallback 캐시도 같이 갱신 (다음 실패 대비)
        Cache::forever(self::FALLBACK_CACHE_KEY, $data);

        return $data;
    }

    private function fallback(): array
    {
        $fallback = Cache::get(self::FALLBACK_CACHE_KEY);

        if ($fallback !== null) {
            Log::info('Instagram 피드: fallback 캐시 데이터 반환');
            return $fallback;
        }

        Log::warning('Instagram 피드: fallback 캐시도 없음, 빈 배열 반환');
        return [];
    }
}
