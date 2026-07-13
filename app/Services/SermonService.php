<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SermonService
{
    private const ALL_CACHE_KEY           = 'sermons:all';
    private const UPCOMING_CACHE_KEY      = 'sermons:upcoming';
    private const LIVE_CACHE_KEY          = 'sermons:live';
    private const NEXT_UPCOMING_CHECK_KEY = 'sermons:next_upcoming_check_at';

    // ══════════════════════════════════════════════════════════════
    // 1. Uploaded video list — no polling, invalidated only by webhook
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
        return collect(Cache::rememberForever(
            self::ALL_CACHE_KEY,
            fn () => $this->fetchAllFromYoutube()->all()
        ));
    }

    /** Called by YoutubeWebhookController when YouTube notifies of a new/updated upload. */
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
                if (!$videoId) {
                    continue;
                }

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
    // 2 & 4. Upcoming live broadcast — fetch once, reuse, retry every 4h if not found
    // ══════════════════════════════════════════════════════════════

    public function getUpcomingSermon(): ?array
    {
        $cached = Cache::get(self::UPCOMING_CACHE_KEY);

        if ($cached !== null) {
            return $cached['sermon'];
        }

        if (!$this->shouldCheckUpcomingNow()) {
            return null;
        }

        $upcoming = $this->fetchUpcomingFromYoutube();

        if ($upcoming !== null) {
            Cache::forever(self::UPCOMING_CACHE_KEY, ['sermon' => $upcoming]);
            Cache::forget(self::NEXT_UPCOMING_CHECK_KEY);
        } else {
            Cache::put(self::NEXT_UPCOMING_CHECK_KEY, now()->addHours(4)->timestamp, now()->addHours(5));
        }

        return $upcoming;
    }

    private function shouldCheckUpcomingNow(): bool
    {
        $nextCheckAt = Cache::get(self::NEXT_UPCOMING_CHECK_KEY);

        return $nextCheckAt === null || now()->timestamp >= $nextCheckAt;
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

    private function resetUpcomingForNextWeek(): void
    {
        Cache::forget(self::UPCOMING_CACHE_KEY);
        Cache::forget(self::NEXT_UPCOMING_CHECK_KEY);
    }

    // ══════════════════════════════════════════════════════════════
    // 3. Live start → end detection (Sunday-only, time-windowed TTL)
    // ══════════════════════════════════════════════════════════════

    public function getLiveSermon(): ?array
    {
        $ttl = $this->liveWindowCacheTtl();

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

        if ($wasLive !== null && $isLiveNow === null) {
            $this->resetUpcomingForNextWeek();
        }

        return $isLiveNow;
    }

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
            return null;
        }

        if ($now->between($preLiveStart, $liveStart)) {
            return 120;
        }

        if ($now->between($liveStart, $endCheckStart)) {
            return 600;
        }

        $lastKnown = Cache::get(self::LIVE_CACHE_KEY);
        if ($lastKnown !== null && $lastKnown['sermon'] === null) {
            return null;
        }

        return 300;
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
    // code change
}
