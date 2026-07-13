<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SubscribeToYoutubeUploads extends Command
{
    protected $signature = 'youtube:subscribe';
    protected $description = 'Subscribe (or renew subscription) to the channel uploads feed via PubSubHubbub';

    public function handle(): int
    {
        $channelId = config('services.youtube.channel_id');
        $topic = "https://www.youtube.com/xml/feeds/videos.xml?channel_id={$channelId}";
        $callback = route('youtube.webhook');

        $payload = [
            'hub.mode'     => 'subscribe',
            'hub.topic'    => $topic,
            'hub.callback' => $callback,
            'hub.verify'   => 'async',
        ];

        if ($secret = config('services.youtube.webhook_secret')) {
            $payload['hub.secret'] = $secret;
        }

        $response = Http::asForm()->post('https://pubsubhubbub.appspot.com/subscribe', $payload);

        if ($response->successful()) {
            $this->info('YouTube PubSubHubbub subscription request sent successfully.');
            return self::SUCCESS;
        }

        $this->error("Subscription request failed: {$response->status()} {$response->body()}");
        return self::FAILURE;
    }
}
