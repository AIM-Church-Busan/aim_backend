<?php

namespace App\Jobs;

use App\Mail\NewsletterMail;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Policy:
 * - Only uses Subscriber::confirmed() — unconfirmed or unsubscribed
 *   subscribers must never be included.
 * - Processes in chunks of 100 to keep memory usage flat as the subscriber
 *   list grows.
 * - Each mail is queued individually via Mail::queue() so one failed send
 *   doesn't affect the rest, and failures can be retried independently.
 */
class DispatchNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $subject, public string $bodyHtml) {}

    public function handle(): void
    {
        Subscriber::confirmed()->chunk(100, function ($subscribers) {
            foreach ($subscribers as $subscriber) {
                Mail::to($subscriber->email)->queue(
                    new NewsletterMail($subscriber, $this->subject, $this->bodyHtml)
                );
            }
        });
    }
}
