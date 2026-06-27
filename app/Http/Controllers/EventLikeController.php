<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\EventLikeService;
use Illuminate\Http\JsonResponse;

class EventLikeController extends Controller
{
    public function __construct(
        private readonly EventLikeService $eventLikeService,
    ) {}

    /**
     * POST /api/events/{event}/like
     * Toggle like for an event. Requires authentication.
     */
    public function toggle(Event $event): JsonResponse
    {
        $liked = $this->eventLikeService->toggleLike($event, auth()->id());

        return response()->json([
            'liked'      => $liked,
            'like_count' => $this->eventLikeService->getLikeCount($event),
        ]);
    }
}
