<?php

namespace App\Console\Commands;

use App\Services\InstagramFeedService;
use Illuminate\Console\Command;

class RefreshInstagramFeed extends Command
{
    protected $signature = 'instagram:refresh-feed';
    protected $description = 'Instagram 피드 캐시를 최신 데이터로 강제 갱신';

    public function handle(InstagramFeedService $service): int
    {
        $feed = $service->refreshFeed();

        $this->info('Instagram 피드 캐시 갱신 완료. 게시물 ' . count($feed) . '개.');

        return self::SUCCESS;
    }
}
