<?php

namespace App\Mail;

use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Policy:
 * - Implements ShouldQueue, so it MUST be dispatched via Mail::queue().
 *   Calling Mail::send() sends synchronously regardless of ShouldQueue,
 *   which would block the request on the Mailtrap round trip — never do this.
 * - confirmUrl points directly at the backend API endpoint (not the frontend)
 *   so it works with a single click from any email client without auth.
 */

class SubscriberConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Subscriber $subscriber) {}

    public function build()
    {
        return $this->subject('구독을 확인해주세요 - AIM Church')
            ->view('emails.subscriber-confirmation')
            ->with(['confirmUrl' => url("/api/subscribers/confirm/{$this->subscriber->token}")]);
    }
}
