<?php

namespace App\Mail;

use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Policy:
 * - This mail must only be sent to subscribers filtered by Subscriber::confirmed()
 *   (enforced by the caller, DispatchNewsletterJob — this class does not validate it).
 * - The body must always include an unsubscribe link. Omitting it violates
 *   anti-spam policy.
 */

class NewsletterMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Subscriber $subscriber,
        public string $newsletterSubject,
        public string $bodyHtml,
    ) {}

    public function build()
    {
        return $this->subject($this->newsletterSubject)
            ->view('emails.newsletter')
            ->with([
                'bodyHtml' => $this->bodyHtml,
                'unsubscribeUrl' => url("/api/subscribers/unsubscribe/{$this->subscriber->token}"),
            ]);
    }
}
