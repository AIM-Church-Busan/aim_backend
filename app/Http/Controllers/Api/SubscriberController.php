<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriberRequest;
use App\Mail\SubscriberConfirmationMail;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;

/**
 * Policy:
 * - store(): keeps exactly one record per email (firstOrCreate) to prevent
 *   duplicate signups. Already-confirmed emails are not re-sent a confirmation mail.
 * - confirm() / unsubscribe() are token-authenticated and require no login —
 *   they must work from a single link click in an email client.
 * - Mail is always dispatched via Mail::queue(). Never use Mail::send().
 */

class SubscriberController extends Controller
{
    public function store(StoreSubscriberRequest $request)
    {
        $subscriber = Subscriber::firstOrCreate(['email' => $request->validated('email')]);

        if ($subscriber->is_confirmed) {
            return response()->json(['message' => 'Already Subscribing.'], 200);
        }

        Mail::to($subscriber->email)->queue(new SubscriberConfirmationMail($subscriber));

        return response()->json(['message' => 'Confirmation mail sent.'], 201);
    }

    public function confirm(string $token)
    {
        $subscriber = Subscriber::where('token', $token)->firstOrFail();
        $subscriber->update(['is_confirmed' => true, 'confirmed_at' => now(), 'unsubscribed_at' => null]);

        return Redirect::away(config('app.frontend_url').'/subscribe/confirmed');
    }

    public function unsubscribe(string $token)
    {
        $subscriber = Subscriber::where('token', $token)->firstOrFail();
        $subscriber->update(['unsubscribed_at' => now()]);

        return Redirect::away(config('app.frontend_url').'/subscribe/unsubscribed');
    }
}
