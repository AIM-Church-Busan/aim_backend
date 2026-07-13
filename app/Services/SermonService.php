<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SermonService
{
    private const ALL_CACHE_KEY      = 'sermons:all';
    private const UPCOMING_CACHE_KEY = 'sermons:upcoming';
    private const LIVE_CACHE_KEY     = 'sermons:live';
    private const NEXT_UPCOMING_CHECK_KEY = 'sermons:next_upcoming_check_at';

    // ══════════════════════════════════════════════════════════════
    // 1. 영상 목록 — 폴링 없음. 웹훅이 캐시를 지울 때만 재조회됨.
    // ══════════════════════════════════════════════════════════════

    public function getSermons(int $page = 1, ?string $title = null, int $perPage = 12): LengthAwarePaginator
    {
        $sermons = $this->getAllSermons();

        if ($title) {
            $sermons = $sermons
                ->filter(fn (array $s) => Str::contains(Str::lower($s['title']), Str::lower($title)))
                ->values();
        }

        $page = max(1, $page);
        $items = $sermons->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $sermons->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function getSermonById(string $id): array
    {
        $sermon = $this->getAllSermons()->firstWhere('id', $id);
        abort_if(is_null($sermon), 404, 'Sermon not found.');
        return $sermon;
    }

    private function getAllSermons(): Collection
    {
        // TTL 없이(사실상 무기한) 유지 — 웹훅이 forget()으로만 무효화시킴
        return collect(Cache::rememberForever(
            self::ALL_CACHE_KEY,
            fn () => $this->fetchAllFromYoutube()->all()
        ));
    }

    /** 웹훅 컨트롤러에서 새 영상 알림 수신 시 호출 */
    public function invalidateAllSermonsCache(): void
    {
        Cache::forget(self::ALL_CACHE_KEY);
    }

    private function fetchAllFromYoutube(): Collection
    {
        $uploadsPlaylistId = $this->getUploadsPlaylistId();
        $videos = collect();
        $pageToken = null;

        do {
            $response = Http::get('https://www.googleapis.com/youtube/v3/playlistItems', [
                'part'       => 'snippet,contentDetails',
                'playlistId' => $uploadsPlaylistId,
                'maxResults' => 50,
                'pageToken'  => $pageToken,
                'key'        => config('services.youtube.api_key'),
            ])->throw()->json();

            foreach ($response['items'] ?? [] as $item) {
                $videoId = $item['contentDetails']['videoId'] ?? null;
                if (!$videoId) continue;

                $videos->push([
                    'id'           => $videoId,
                    'title'        => $item['snippet']['title'] ?? '',
                    'description'  => $item['snippet']['description'] ?? '',
                    'published_at' => $item['contentDetails']['videoPublishedAt']
                        ?? $item['snippet']['publishedAt'] ?? null,
                    'thumbnail'    => $item['snippet']['thumbnails']['high']['url']
                        ?? $item['snippet']['thumbnails']['default']['url'] ?? null,
                    'video_url'    => "https://www.youtube.com/watch?v={$videoId}",
                ]);
            }

            $pageToken = $response['nextPageToken'] ?? null;
        } while ($pageToken);

        return $videos->sortByDesc('published_at')->values();
    }

    private function getUploadsPlaylistId(): string
    {
        return Cache::rememberForever('sermons:uploads_playlist_id', function () {
            $response = Http::get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'contentDetails',
                'id'   => config('services.youtube.channel_id'),
                'key'  => config('services.youtube.api_key'),
            ])->throw()->json();

            return $response['items'][0]['contentDetails']['relatedPlaylists']['uploads']
                ?? throw new \RuntimeException('Could not resolve YouTube uploads playlist ID.');
        });
    }

    // ══════════════════════════════════════════════════════════════
    // 2 & 4. Upcoming Live — 확보되면 고정 사용, 없으면 4시간 간격 재시도
    // ══════════════════════════════════════════════════════════════

    public function getUpcomingSermon(): ?array
    {
        $cached = Cache::get(self::UPCOMING_CACHE_KEY);

        // 이미 확보된 upcoming이 있으면 그대로 반환, API 호출 없음
        if ($cached !== null) {
            return $cached['sermon'];
        }

        // 아직 확보된 게 없을 때만, 재확인 시점(next_upcoming_check_at)이 됐는지 체크
        if (!$this->shouldCheckUpcomingNow()) {
            return null;
        }

        $upcoming = $this->fetchUpcomingFromYoutube();

        if ($upcoming !== null) {
            // 찾았다 — 다음 주 라이브가 끝날 때까지 무기한 캐싱, 재시도 타이머 삭제
            Cache::forever(self::UPCOMING_CACHE_KEY, ['sermon' => $upcoming]);
            Cache::forget(self::NEXT_UPCOMING_CHECK_KEY);
        } else {
            // 아직 없다 — 4시간 뒤 다시 확인하도록 예약
            Cache::put(self::NEXT_UPCOMING_CHECK_KEY, now()->addHours(4)->timestamp, now()->addHours(5));
        }

        return $upcoming;
    }

    private function shouldCheckUpcomingNow(): bool
    {
        $nextCheckAt = Cache::get(self::NEXT_UPCOMING_CHECK_KEY);

        // 예약된 재시도 시각이 없으면(최초 1회) 바로 확인
        if ($nextCheckAt === null) {
            return true;
        }

        return now()->timestamp >= $nextCheckAt;
    }

    private function fetchUpcomingFromYoutube(): ?array
    {
        $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
            'part'       => 'snippet',
            'channelId'  => config('services.youtube.channel_id'),
            'eventType'  => 'upcoming',
            'type'       => 'video',
            'order'      => 'date',
            'maxResults' => 1,
            'key'        => config('services.youtube.api_key'),
        ])->throw()->json();

        $item = $response['items'][0] ?? null;
        if (!$item || !isset($item['id']['videoId'])) {
            return null;
        }

        $videoId = $item['id']['videoId'];

        return [
            'id'          => $videoId,
            'title'       => $item['snippet']['title'] ?? '',
            'description' => $item['snippet']['description'] ?? '',
            'thumbnail'   => $item['snippet']['thumbnails']['high']['url']
                ?? $item['snippet']['thumbnails']['default']['url'] ?? null,
            'video_url'   => "https://www.youtube.com/watch?v={$videoId}",
        ];
    }

    /** 라이브 종료가 확인된 시점에 호출 — 다음 주 upcoming 재탐색을 시작시킴 */
    private function resetUpcomingForNextWeek(): void
    {
        Cache::forget(self::UPCOMING_CACHE_KEY);
        Cache::forget(self::NEXT_UPCOMING_CHECK_KEY);
    }

    // ══════════════════════════════════════════════════════════════
    // 3. Live 시작 → 종료 감지 (일요일 시간대 기반 동적 폴링)
    // ══════════════════════════════════════════════════════════════

    public function getLiveSermon(): ?array
    {
        $ttl = $this->liveWindowCacheTtl();

        // ttl === null → 이 구간에서는 API 호출 자체를 안 함, 캐시도 안 건드림
        if ($ttl === null) {
            return null;
        }

        $wasLive = Cache::get(self::LIVE_CACHE_KEY)['sermon'] ?? null;

        $cached = Cache::remember(
            self::LIVE_CACHE_KEY,
            $ttl,
            fn () => ['sermon' => $this->fetchLiveFromYoutube()]
        );

        $isLiveNow = $cached['sermon'];

        // 직전엔 라이브였는데 이번 체크에서 null이 됐다 = 방금 종료됨
        if ($wasLive !== null && $isLiveNow === null) {
            $this->resetUpcomingForNextWeek();
        }

        return $isLiveNow;
    }

    /**
     * 시간대별 폴링 TTL 결정.
     * null = 이 시간대엔 아예 확인하지 않음 (평일/이른 시간)
     */
    private function liveWindowCacheTtl(): ?int
    {
        $now = now();

        if (!$now->isSunday()) {
            return null;
        }

        $preLiveStart  = $now->copy()->setTime(10, 50);
        $liveStart     = $now->copy()->setTime(11, 0);
        $endCheckStart = $now->copy()->setTime(12, 0);

        if ($now->lt($preLiveStart)) {
            return null; // 일요일이지만 아직 이른 시간 — 확인 안 함
        }

        if ($now->between($preLiveStart, $liveStart)) {
            return 120; // 10:50~10:59 — 시작 감지, 2분 주기
        }

        if ($now->between($liveStart, $endCheckStart)) {
            return 600; // 11:00~11:59 — 이미 시작 확인됨, 10분 주기로 느슨하게
        }

        // 12:00 이후 — 종료될 때까지 5분 주기 유지 (하드 컷오프 없음)
        $lastKnown = Cache::get(self::LIVE_CACHE_KEY);
        if ($lastKnown !== null && $lastKnown['sermon'] === null) {
            return null; // 이미 종료 확인됨 — 더 이상 안 물어봄
        }

        return 300; // 5분
    }

    private function fetchLiveFromYoutube(): ?array
    {
        $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
            'part'      => 'snippet',
            'channelId' => config('services.youtube.channel_id'),
            'eventType' => 'live',
            'type'      => 'video',
            'key'       => config('services.youtube.api_key'),
        ])->throw()->json();

        $item = $response['items'][0] ?? null;
        if (!$item || !isset($item['id']['videoId'])) {
            return null;
        }

        $videoId = $item['id']['videoId'];

        return [
            'id'          => $videoId,
            'title'       => $item['snippet']['title'] ?? '',
            'description' => $item['snippet']['description'] ?? '',
            'thumbnail'   => $item['snippet']['thumbnails']['high']['url']
                ?? $item['snippet']['thumbnails']['default']['url'] ?? null,
            'video_url'   => "https://www.youtube.com/watch?v={$videoId}",
            'is_live'     => true,
        ];
    }
}
