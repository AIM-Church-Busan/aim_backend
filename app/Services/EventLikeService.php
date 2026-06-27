<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventLike;

class EventLikeService
{
    /**
     * Toggle like for an event.
     * Returns true if liked, false if unliked.
     */
    public function toggleLike(Event $event, int $userId): bool
    {
        $like = EventLike::where('event_id', $event->id)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();
            return false;
        }

        EventLike::create([
            'event_id' => $event->id,
            'user_id'  => $userId,
        ]);

        return true;
    }

    /**
     * Check if a user has liked an event.
     */
    public function isLiked(Event $event, int $userId): bool
    {
        return EventLike::where('event_id', $event->id)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get the total like count for an event.
     */
    public function getLikeCount(Event $event): int
    {
        return EventLike::where('event_id', $event->id)->count();
    }
}
